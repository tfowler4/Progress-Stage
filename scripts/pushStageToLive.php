<?php 

include 'script.php';

class PushStageToLive {
    static protected $_gameName;
    static protected $_destination;
    static protected $_newFileLocation;
    static protected $_destinationFolder;
    static protected $_currentFileLocation;
    static protected $_numberOfFiles = 0;
    static protected $_exemptFolders = array('docs', 'images', 'temp', 'data', '.git');
    
    const SOURCE_FOLDER              = ABSOLUTE_PATH;

    static public function init() {
        Logger::log('INFO', 'Starting Push Stage To Live...');

        $folders       = explode("/", self::SOURCE_FOLDER);
        $remove_folder = array_pop($folders);
        array_push($folders, 'site-');

        self::$_destinationFolder = implode("/", $folders);

        if ( isset($_GET['gameName']) ) {
            self::$_gameName    = strtolower($_GET['gameName']);
            self::$_destination = self::$_destinationFolder . self::$_gameName;
        }

        if ( file_exists(self::$_destination) ) {
            Logger::log('INFO', 'Starting Search Directory in ' . self::$_gameName . '...');

            self::searchDirectory(self::SOURCE_FOLDER);
            
            Logger::log('INFO', 'Search Directory in ' . self::$_gameName . ' Completed!');
            Logger::log('INFO', 'Total Number of Files Updated: ' . self::$_numberOfFiles);
        }
        Logger::log('INFO', 'Push Stage To Live Completed!');
    }
    
    static public function searchDirectory($currentFolder) {
        $liveFiles     = array();
        $stageFiles    = array();
        $modifiedFiles = array();

        $handle = opendir($currentFolder);
        
        while ( false !== ($file = readdir($handle)) ) {
            self::$_currentFileLocation = $currentFolder . '/' . $file;

            if ( (isset(self::$_gameName)) ) {
                self::$_newFileLocation = str_replace(self::SOURCE_FOLDER, self::$_destination, self::$_currentFileLocation);
            }

            if ( self::checkExemptions(self::$_currentFileLocation) == FALSE) {
                if ( ($file != '.') && ($file != '..') ) {
                    if ( is_dir(self::$_currentFileLocation) ) {
                        self::checkDirectory(self::$_currentFileLocation);
                        self::searchDirectory(self::$_currentFileLocation);
                    } else if ( !is_dir(self::$_currentFileLocation) ) {
                        // To get files from stage and live before the copy
                        $stageFiles = self::getCurrentFiles(self::$_currentFileLocation);
                        $liveFiles  = self::getCurrentFiles(self::$_newFileLocation);
                        // To compare modified files with live files and prepare for logging
                        $modifiedFiles = self::compareModifiedFiles($stageFiles, $liveFiles);
                        if ( !empty($modifiedFiles) ) {
                            self::logModifiedFiles($modifiedFiles);
                        }
                        // To copy files from stage to live
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
        $folders = explode('/', $folderName);

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

    static public function getCurrentFiles($file) {
        $files = array();
        if ( is_file($file) ) {
            $modifiedTime = filemtime($file);
            $folders      = explode('/', $file);
            $currentFile  = array_pop($folders);
            $files[]      = array('fileName' => $currentFile, 'modifiedTime' => $modifiedTime);
        }
        return $files;
    }

    static public function compareModifiedFiles($stageFiles, $liveFiles) {
        $modifiedFiles = array();

        if ( is_array($stageFiles) && is_array($liveFiles) ) {
            foreach ($stageFiles as $stageFile) {
                $modified = true;

                foreach ($liveFiles as $liveFile) {
                    if ( ($stageFile['fileName'] == $liveFile['fileName']) && ($stageFile['modifiedTime'] < $liveFile['modifiedTime']) ) {
                        $modified = false;
                    }
                }

                if ($modified == true) {
                    $modifiedFiles[] = $stageFile;
                }
                return $modifiedFiles;
            }
        } 
    }

    static public function logModifiedFiles($files) {
        self::$_numberOfFiles += count($files);

        if ( is_array($files) ) {
            foreach ($files as $file) {
                $fileInfo = 'File: ' . $file['fileName'] . ' Last Modified: ' . date ("F d Y H:i:s", $file['modifiedTime']);
                Logger::log('INFO', $fileInfo);
            }
        }
    }
}

PushStageToLive::init();

?>
