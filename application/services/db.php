<?php
class Db {
    protected static $_dbh;

    public static function init() {
        try {
            self::$_dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=UTF8", DB_USER, DB_PASS);
            self::$_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
        } catch ( PDOException $e ) {
            die('Connection error: ' . $e->getMessage());
        }
    }
    
    public static function getDbh() {
        return self::$_dbh;
    }
}

Db::init();