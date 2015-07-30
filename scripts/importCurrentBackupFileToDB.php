<?php
include 'script.php';

class importCurrentBackupFileToDB {
	static protected $_login;
	static protected $_connection;
    static protected $_serverBackupPath;

    const LOCAL_PATH = ABSOLUTE_PATH . '/temp';

	static public function init() {
		$dateYear  = date('Y');
        $dateMonth = date('n')."-".date('M');
        $dateDay   = date('d');
        $fullDate  = $dateYear . '/' . $dateMonth . '/' . $dateDay;

        self::$_serverBackupPath = 'public_html/' . DOMAIN . '/data/backups/'. $fullDate;
        self::$_connection       = ftp_connect(FTP_HOST);
        self::$_login            = ftp_login(self::$_connection, FTP_USER, FTP_PASSWORD);

        // To check connection and login
        if ( (!self::$_connection) || (!self::$_login) ) {
            die;
        }

        // To set the backup file
        $lastestBackupFile = self::getCurrentBackupFile(self::$_connection, self::$_serverBackupPath);

        // To set the import file
        $importFile = self::downloadBackupFile(self::$_connection, $lastestBackupFile);

        self::executeImportCommand($importFile);

        ftp_close(self::$_connection);
	}

    // To import file into database
    static public function executeImportCommand($file) {

        // To import file into target database; DB must already exist
        $command ='C:\xampp\mysql\bin\mysql -h' . DB_HOST .' -u' . DB_USER .' -p' . DB_PASS .' ' .  DB_NAME .' < ' . $file; //. ' > stdout_output.txt 2>stderr_output.txt';

        exec($command, $output=array(), $msg);

        switch($msg) {
            case 0:
                echo 'SUCCESS: Import Completed!';
                break;
            case 1:
                echo 'ERROR: Import Failed!';
                break;
        }
    }

    // To download lastest file to local
    static public function downloadBackupFile($connection, $file) {
        if ( !file_exists(self::LOCAL_PATH) ) {
            mkdir(self::LOCAL_PATH, 0777, true);
        }

        $localFile = str_replace(self::$_serverBackupPath, self::LOCAL_PATH, $file);

        if ( !ftp_get($connection, $localFile, $file, FTP_BINARY) ) {
            echo 'Error Downloading: ' . $file . '<br>';
            exit;
        }
        return $localFile;
    }

	// To get lastest backup file from web server
	static public function getCurrentBackupFile($connection, $path) {
        if (DOMAIN == 'stage') {
            echo 'ERROR: Please execute script from the correct domain!';
            exit;
        }

		$listings   = ftp_nlist($connection, $path);
        $count      = count($listings);
        $lastestFile = $listings[$count - 1];
        
        return $path . '/' . $lastestFile;
	}
}

importCurrentBackupFileToDB::init();

