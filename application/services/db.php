<?php
class Db {
    protected static $_dbh;

    private function __construct() {
        try {
            self::$_dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=UTF8", DB_USER, DB_PASS);
            self::$_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $e ) {
            die('Connection error: ' . $e->getMessage());
        }
    }

    public static function init() {
        if ( !self::$_dbh ) {
            new Db();
        }
    }
    
    public static function getDbh() {
        if ( !self::$_dbh ) {
            new Db();
        }

        return self::$_dbh;
    }

    public function __clone() {}
}

Db::init();