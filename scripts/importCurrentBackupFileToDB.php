<?php
include 'script.php';

class importCurrentBackupFileToDB {
	static protected $_login;
	static protected $_connection;
	static protected $_backupPath;

	static public function init() {
		$dateYear  = date('Y');
        $dateMonth = date('n')."-".date('M');
        $dateDay   = date('d');
        $fullDate  = $dateYear . '/' . $dateMonth . '/' . $dateDay;

        //public_html/site-wildstar/data/backups/2015/7-Jul/26
        self::$_backupPath = FOLD_BACKUPS. $fullDate;
        self::$_connection = ftp_connect(FTP_HOST);
        self::$_login      = ftp_login(self::$_connection, FTP_USER, FTP_PASSWORD);

        // To check connection and login
        if ( (!self::$_connection) || (!self::$_login) ) {
            die;
        } else {
        	echo 'CONNECTED!';
        }

        self::getCurrentBackupFile();
        //self::getCurrentBackupFile(self::$_connection, self::$_backupPath);
	}

	// To get current backup file from web server
	static public function getCurrentBackupFile() {
		$listings = ftp_nlist(self::$_connection, self::$_backupPath);
	}
}

importCurrentBackupFileToDB::init();