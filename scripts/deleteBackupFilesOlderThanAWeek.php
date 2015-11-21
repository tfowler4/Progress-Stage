<?php

include 'script.php';

class DeleteBackupFilesOlderThanAWeek extends Script {
    protected static $_login;
    protected static $_connection;
    protected static $_serverBackupPath;
    protected static $_fileListings = array();

    public static function init() {
        Logger::log('INFO', 'Starting Delete Backup Files Older Than A Week...', 'dev');

        self::$_serverBackupPath = 'public_html/' . DOMAIN . '/data/backups/';
        self::$_connection       = ftp_connect(FTP_HOST);
        self::$_login            = ftp_login(self::$_connection, FTP_USER, FTP_PASSWORD);

        // To check connection and login
        if ( (!self::$_connection) || (!self::$_login) ) {
            die;
        }

        self::$_fileListings = self::getFileListings(self::$_connection, self::$_serverBackupPath);

        self::searchListings(self::$_fileListings);

        Logger::log('INFO', 'Delete Backup Files Older Than A Week Completed!', 'dev');
    }

    // To get file listings in backup directory
    public static function getFileListings($connection, $path) {
        if (DOMAIN == 'stage') {
            Logger::log('ERROR', 'Failed to execute script in current domain: ' . DOMAIN, 'dev');
            exit;
        }

        $listings = ftp_nlist($connection, $path);

        foreach($listings as $currentFile) {
            if ( ($currentFile != '.') && ($currentFile != '..') ) {
                $fileLocation = $path . '/' . $currentFile;
                array_push(self::$_fileListings, $fileLocation);

                if ( strpos($currentFile, '.') === false) {
                    self::getFileListings($connection, $fileLocation);
                }
            }
        }
        return self::$_fileListings;
    }

    // To search through the file listings and obtain modified file time
    public static function searchListings($currentListing) {
        $currentTime = time();

        foreach($currentListing as $currentFileLocation) {
            $modifiedTime = ftp_mdtm(self::$_connection, $currentFileLocation);

            if ( ($currentTime - $modifiedTime) >= 604800 ) { // 7 days old
                if ( $modifiedTime != -1 ) {
                    self::deleteBackupFiles(self::$_connection, $currentFileLocation);
                }
            }
        }
    }

    // To delete necessary backup files
    public static function deleteBackupFiles($connection, $path) {
        if ( !ftp_delete($connection, $path) ) {
            Logger::log('ERROR', 'Unable to delete backup file: ' . $path, 'dev');
        }
    }
}

DeleteBackupFilesOlderThanAWeek::init();