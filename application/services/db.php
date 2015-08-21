<?php

/**
 * database handling class
 */
class Db {
    protected static $_dbh;

    /**
     * constructor
     */
    private function __construct() {
        try {
            self::$_dbh = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=UTF8", DB_USER, DB_PASS);
            self::$_dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch ( PDOException $e ) {
            die('Connection error: ' . $e->getMessage());
        }
    }

    /**
     * initialize a database object if one doesn't already exist
     * 
     * @return void
     */
    public static function init() {
        if ( !self::$_dbh ) {
            new Db();
        }
    }

    /**
     * get a database handler, create a new one if one doesn't exist
     * 
     * @return self
     */
    public static function getDbh() {
        if ( !self::$_dbh ) {
            new Db();
        }

        return self::$_dbh;
    }

    /**
     * magic clone
     */
    public function __clone() {}
}

Db::init();