<?php

include 'script.php';

class BackupDatabase extends Script {
    protected static $backupFileName;
    protected static $backupPath;
    protected static $oldBackupPath;

    public static function init() {
        Logger::log('INFO', 'Starting Backup Database...', 'dev');

        self::$backupFileName = DB_NAME . date("Y-m-d_H-i"). '.sql';

        self::executeDatabaseCommand();
    }

    public static function executeDatabaseCommand() {
        $command = 'mysqldump --no-defaults -h' . DB_HOST . ' -u' . DB_USER . ' -p' . DB_PASS . ' ' . DB_NAME . ' > ' . self::$backupFileName; // > stdout_output.txt 2>stderr_output.txt
        exec($command);

        Logger::log('INFO', 'Backup File: ' . self::$backupFileName . '...', 'dev');

        self::moveFile();
    }

    public static function moveFile() {
        $dateYear  = date('Y');
        $dateMonth = date('n')."-".date('M');
        $dateDay   = date('d');
        $fullDate  = $dateYear . '/' . $dateMonth . '/' . $dateDay;

        self::$oldBackupPath = FOLD_SCRIPTS . self::$backupFileName;
        self::$backupPath    = FOLD_BACKUPS . $fullDate;

        if ( !file_exists(self::$backupPath) ) {
            if ( !mkdir(self::$backupPath, 0777, true) ) {
                Logger::log('ERROR', 'Folder unable to be created at location: ' . self::$backupPath, 'dev');
            }
        }

        self::$backupPath = self::$backupPath . '/' . self::$backupFileName;

        rename(self::$oldBackupPath, self::$backupPath);

        Logger::log('INFO', 'Backup Database Completed!', 'dev');
    }
}

BackupDatabase::init();