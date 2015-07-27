<?php
include 'script.php';

class importCurrentBackupFileToDB {
	static protected $_login;
	static protected $_connection;
    static protected $_serverBackupPath;
    //C:/xampp/htdocs/stage/tmp
    const LOCAL_PATH = ABSOLUTE_PATH . '/tmp';

	static public function init() {
		$dateYear  = date('Y');
        $dateMonth = date('n')."-".date('M');
        $dateDay   = date('d');
        $fullDate  = $dateYear . '/' . $dateMonth . '/' . $dateDay;

        //public_html/site-rift/data/backups/2015/7-Jul/26
        self::$_serverBackupPath = 'public_html/site-rift/data/backups/'. $fullDate;
        self::$_connection       = ftp_connect(FTP_HOST);
        self::$_login            = ftp_login(self::$_connection, FTP_USER, FTP_PASSWORD);

        // To check connection and login
        if ( (!self::$_connection) || (!self::$_login) ) {
            die;
        }
        //public_html/site-rift/data/backups/2015/7-Jul/27/vgtrin5_rift_live2015-07-27_10-00.sql
        $lastestBackupFile = self::getCurrentBackupFile(self::$_connection, self::$_serverBackupPath);
        //print_r($lastestBackupFile);
        $importFile = self::downloadBackupFile(self::$_connection, $lastestBackupFile));
        ftp_close(self::$_connection);
	}

    // To import file into database
    static public function executeImportCommand($file) {
        $command='mysql -h' . DB_HOST .' -u' . DB_USER .' -p' . DB_PASS .' ' . DB_NAME .' < ' . $file;
        exec($command);
    }

    // To download lastest file to local
    static public function downloadBackupFile($connection, $file) {
        $localFile = str_replace(self::$_serverBackupPath, self::LOCAL_PATH, $file);

        if ( !ftp_get($connection, $localFile, $file, FTP_BINARY) ) {
            echo 'Error Downloading: ' . $file . '<br>';
            exit;
        }

        return $localFile;
    }

	// To get lastest backup file from web server
	static public function getCurrentBackupFile($connection, $path) {
		$listings   = ftp_nlist($connection, $path);
        $count      = count($listings);
        $lastestFile = $listings[$count - 1];
        
        return $path . '/' . $lastestFile;
	}
}

importCurrentBackupFileToDB::init();

//$command='mysql -h' .$mysqlHostName .' -u' .$mysqlUserName .' -p' .$mysqlPassword .' ' .$mysqlDatabaseName .' < ' .$mysqlImportFilename;