<?php 

include 'script.php';

class pushStageToLive {
    static protected $_gameName;
    static protected $_destination;
    static protected $_newFileLocation;
    static protected $_destinationFolder;
    static protected $_currentFileLocation;
    static protected $_exemptFolders = array('docs', 'images', 'scripts', 'temp');
    
    const SOURCE_FOLDER = ABSOLUTE_PATH;

    static public function init() {
        $folders = explode("/", self::SOURCE_FOLDER);
        $replace_folder = array(3 => 'live-');
        $folder = array_replace($folders, $replace_folder);
        self::$_destinationFolder = implode("/", $folder);

        if ( isset($_GET['gameName']) ) {
            self::$_gameName = $_GET['gameName'];
            self::$_destination = self::$_destinationFolder . self::$_gameName;

            if ( file_exists(self::$_destination) ) {
                self::searchDirectory(self::SOURCE_FOLDER);
            } 
        } 
    }
    
    static public function searchDirectory($currentFolder) {

        $handle = opendir($currentFolder);
        
        while ( false !== ($file = readdir($handle)) ) {
            self::$_currentFileLocation = $currentFolder . '\\' . $file;

            if ( (isset(self::$_gameName)) ) {
                self::$_newFileLocation = str_replace(self::SOURCE_FOLDER, self::$_destination, self::$_currentFileLocation);
            }

            self::checkExemptions(self::$_currentFileLocation);

            if ( self::checkExemptions(self::$_currentFileLocation) == FALSE) {

                if ( ($file != '.') && ($file != '..') ) {
                    if ( is_dir(self::$_currentFileLocation) ) {
                        self::checkDirectory(self::$_currentFileLocation);
                        self::searchDirectory(self::$_currentFileLocation);
                    } else if ( !is_dir(self::$_currentFileLocation) ) {
                        self::moveFiles(self::$_currentFileLocation, self::$_newFileLocation);
                    }
                }
            }
        }
        closedir($handle);  
   }
    
    static public function checkDirectory($file) {

            if ( (isset(self::$_gameName)) ) {
                $directory = str_replace(self::SOURCE_FOLDER, self::$_destination, $file);
            } 

        if( !is_dir($directory)) {
            mkdir($directory, 0777, true);
        }
    }
    
    static public function moveFiles($oldFile, $newFile) {
        copy($oldFile, $newFile);
    }

    static public function checkExemptions($folderName) {
        $folders = explode('\\', $folderName);

            if ( (isset(self::$_gameName)) ) {
                if (self::$_gameName == 'rift') {
                    array_push(self::$_exemptFolders, 'wildstar');
                } else if (self::$_gameName == 'wildstar') {
                    array_push(self::$_exemptFolders, 'rift');
                }
                
            } 

        foreach (self::$_exemptFolders as $exemption) {
            if ( in_array($exemption, $folders) ) {
                return true;
            }
        }
    }
}

pushStageToLive::init();

?>
