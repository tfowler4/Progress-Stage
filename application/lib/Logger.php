<?php

/**
 * class to log data in either database or text files
 */
class Logger {
    protected static $_logPath;
    protected static $_logFile;
    protected static $_logDate;
    protected static $_severity;
    protected static $_message;
    protected static $_fileHandle;
    protected static $_logType;

    /**
     * creates logging message
     * 
     * @param  string $severity [ log message severity status ]
     * @param  string $message  [ log message ]
     * 
     * @return void
     */
    public static function log($severity, $message, $logType = 'user') {
        // INFO DEBUG WARN ERROR
        self::$_severity = $severity;
        self::$_message  = $message;
        self::$_logType  = $logType;

        $year           = date('Y');
        $month          = date('n')."-".date('M');
        $currentDate    = date('Y-m-d');
        self::$_logDate = date('Y-m-d H:i');

        if ( self::$_logType == 'user' ) {
            self::$_logPath = strtolower(FOLD_LOGS . '/user/' . $year . '/' . $month);
        } elseif ( self::$_logType == 'dev' ) {
            self::$_logPath = strtolower(FOLD_LOGS . '/dev/' . $year . '/' . $month);
        }

        self::$_logFile = strtolower(self::$_logPath . '/' . SITE_TITLE . '-' . $currentDate . '.txt');

        if ( !file_exists(self::$_logPath) ) { 
            mkdir(self::$_logPath, 0777, true); 
        }

        self::$_message = preg_replace('/\s+/', ' ', trim(self::$_message));

        self::writeToFile();
        self::writeToDB();
    }

    /**
     * write log message to text file
     * 
     * @return void
     */
    public static function writeToFile() {
        self::$_fileHandle = fopen(self::$_logFile, 'a+');
        fwrite(self::$_fileHandle, self::$_severity . ' | ' . self::$_logDate . ' | ' . session_id() . ' | ' . self::$_message . "\n");
        fclose(self::$_fileHandle);
    }

    /**
     * place log message into database entry
     * 
     * @return void
     */
    public static function writeToDB() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "INSERT INTO %s
            (severity, type, session_id, message)
            values('%s','%s','%s',\"%s\")",
            DbFactory::TABLE_LOGGING,
            self::$_severity,
            self::$_logType,
            session_id(),
            self::$_message
        ));
        $query->execute();
    }
}