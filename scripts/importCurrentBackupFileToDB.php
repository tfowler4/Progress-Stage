<?php
include 'script.php';

class ImportCurrentBackupFileToDB {
	static protected $_login;
	static protected $_connection;
    static protected $_serverBackupPath;

    const LOCAL_PATH = ABSOLUTE_PATH . '/temp';

	static public function init() {
        Logger::log('INFO', 'Starting Import Current Backup File to Database...', 'dev');

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
        $command ='mysql -h' . DB_HOST .' -u' . DB_USER .' -p' . DB_PASS .' ' .  DB_NAME .' < ' . $file; //. ' > stdout_output.txt 2>stderr_output.txt';

        exec($command, $output=array(), $msg);

        switch($msg) {
            case 0:
                Logger::log('INFO', 'Import Current Backup File to Database Completed!', 'dev');
                break;
            case 1:
                Logger::log('ERROR', 'Unable to import backup file: ' . $file, 'dev');
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
            Logger::log('ERROR', 'Unable to download file: ' . $file, 'dev');
            exit;
        }
        return $localFile;
    }

	// To get lastest backup file from web server
	static public function getCurrentBackupFile($connection, $path) {
        if (DOMAIN == 'stage') {
            Logger::log('ERROR', 'Failed to execute script in current domain: ' . DOMAIN, 'dev');
            exit;
        }

		$listings   = ftp_nlist($connection, $path);
        $count      = count($listings);
        $lastestFile = $listings[$count - 1];
        
        return $path . '/' . $lastestFile;
	}
}

ImportCurrentBackupFileToDB::init();

