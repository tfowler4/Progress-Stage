<?php

include 'script.php';

class GeneratePassword extends Script {
    protected static $_unEncryptedPassword;
    protected static $_encryptedPassword;

    public static function init() {
        Logger::log('INFO', 'Starting Generate Password...');

        if ( !empty($_GET['password']) ) {
            self::$_unEncryptedPassword = $_GET['password'];
            self::encryptPassword(self::$_unEncryptedPassword);
            self::displayPassword();

            Logger::log('INFO', 'Generate Password Completed!');
        } else {
            echo "Please provide a password for encryption";
        }
    }

    public static function encryptPassword($password) {
        self::$_encryptedPassword = sha1($password);

        for ( $i = 0; $i < 5; $i++ ) {
            self::$_encryptedPassword = sha1(self::$_encryptedPassword.$i);
        }

        crypt(self::$_encryptedPassword, '');
    }

    public static function displayPassword() {
        echo 'Unencrypted Password: ' . self::$_unEncryptedPassword;
        echo '<br>';
        echo 'Encrypted Password: ' . self::$_encryptedPassword;;
    }
}

GeneratePassword::init();