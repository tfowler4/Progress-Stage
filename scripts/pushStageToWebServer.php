<?php

include 'script.php';

class pushStageToWebServer {
    static protected $_login;
    static protected $_connection;
    static protected $_localFileLocation;
    static protected $_remoteFileLocation;
    static protected $_serverFiles     = array();
    static protected $_serverDirectory = array();
    static protected $_exemptFolders   = array('data', 'images', 'docs', '.git');

    // Local location is set to ABSOLUTE_PATH
    const REMOTE_LOCATION = '/public_html/' . DOMAIN;

    static public function init() {
        self::$_connection = ftp_connect(FTP_HOST);
        self::$_login      = ftp_login(self::$_connection, FTP_USER, FTP_PASSWORD);

        // To check connection and login
        if ( (!self::$_connection) || (!self::$_login) ) {
            die;
        } 

        // To set listing of server files
        self::$_serverDirectory = self::getWebServerListings(self::$_connection, self::REMOTE_LOCATION);

        if ( file_exists(ABSOLUTE_PATH) ) {
            self::searchDirectory(ABSOLUTE_PATH);
        }
        ftp_close(self::$_connection);
    }

    // To search thru each directory
    static public function searchDirectory($currentFolder) {
        $handle = opendir($currentFolder);

        while ( false !== ($file = readdir($handle)) ) {
            self::$_localFileLocation   = $currentFolder . '/' . $file;
            self::$_remoteFileLocation  = str_replace(ABSOLUTE_PATH, self::REMOTE_LOCATION, self::$_localFileLocation);

            if ( self::checkExemptions(self::$_localFileLocation) == FALSE) {
                if ( ($file != '.') && ($file != '..') ) {
                    if ( is_dir(self::$_localFileLocation) ) {
                        self::checkDirectory(self::$_localFileLocation);
                        self::searchDirectory(self::$_localFileLocation);
                    } else if ( !is_dir(self::$_localFileLocation) ) {
                        self::uploadFiles(self::$_localFileLocation, self::$_remoteFileLocation);
                    }
                }
            }
        }
        closedir($handle);
    }

    // To check directories and create them if they do not exist on web server
    static public function checkDirectory($file) {
        $directory = str_replace(ABSOLUTE_PATH, self::REMOTE_LOCATION, $file);

        if ( !is_dir($directory) ) {
            if ( !in_array($directory, self::$_serverDirectory) ) {
                ftp_mkdir(self::$_connection, $directory);
            }
        }
    }

    // To check directories and exclude those on the list
    static public function checkExemptions($folderName) {
        $folders = explode('/', $folderName);

        foreach (self::$_exemptFolders as $exemption) {
            if ( in_array($exemption, $folders) ) {
                return true;
            }
        }
    }

    // To upload files from local machine to web server
    static public function uploadFiles($localFile, $remoteFile) {
        $upload = ftp_nb_put(self::$_connection, $remoteFile, $localFile, FTP_BINARY);

        // Keep connection open while files are uploading
        while ($upload == FTP_MOREDATA) {
            $upload = ftp_nb_continue(self::$_connection);
        }
        // Display message if upload errors out
        if ($upload != FTP_FINISHED) {
            echo 'Error Uploading: ' . $localFile . '<br>';
            exit;
        }
    }

    // To get all file listings from web server
    static public function getWebServerListings($connection, $path) {
        $listings = ftp_nlist($connection, $path);

        foreach ($listings as $currentFile) {
            if ( ($currentFile != '.') && ($currentFile != '..') ) {
                self::$_remoteFileLocation = $path . '/' . $currentFile;

                if ( self::checkExemptions($currentFile) == FALSE) {
                    array_push(self::$_serverFiles, self::$_remoteFileLocation);

                    if ( strpos($currentFile, '.') === false) {
                        self::getWebServerListings($connection, self::$_remoteFileLocation);
                    }
                }
            }
        }
        return self::$_serverFiles;
    }
}

pushStageToWebServer::init();
