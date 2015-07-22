<?php
class Logger {
    static protected $_logPath;
    static protected $_logFile;
    static protected $_logDate;
    static protected $_severity;
    static protected $_message;
    static protected $_fileHandle;
    
    static public function log($severity, $message) {
        // INFO DEBUG WARN ERROR
        self::$_severity = $severity;
        self::$_message  = $message;

        $year           = date('Y');
        $month          = date('n')."-".date('M');
        $currentDate    = date('Y-m-d');
        self::$_logDate = date('Y-m-d H:i');

        self::$_logPath = strtolower(FOLD_LOGS . $year . '/' . $month);
        self::$_logFile = strtolower(self::$_logPath . '/' . SITE_TITLE . '-' . $currentDate . '.txt');

        if ( !file_exists(self::$_logPath) ) { 
            mkdir(self::$_logPath, 0777, true); 
        }

        self::$_message = preg_replace('/\s+/', ' ', trim(self::$_message));

        self::writeToFile();
        self::writeToDB();
    }

    static public function writeToFile() {
        self::$_fileHandle = fopen(self::$_logFile, 'a+');
        fwrite(self::$_fileHandle, self::$_severity . ' | ' . self::$_logDate . ' | ' . session_id() . ' | ' . self::$_message . "\n");
        fclose(self::$_fileHandle);
    }

    static public function writeToDB() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "INSERT INTO %s
            (severity, session_id, message)
            values('%s','%s',\"%s\")",
            DbFactory::TABLE_LOGGING,
            self::$_severity,
            session_id(),
            self::$_message
        ));
        $query->execute();
    }
}