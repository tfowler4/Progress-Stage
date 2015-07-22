<?php
date_default_timezone_set('America/Los_Angeles');

// Global Settings
define('GAME_NAME_1',       'Rift');
define('HOST_NAME',         'http://localhost/stage');
define('DOMAIN',            'stage');
define('DEFAULT_TIME_ZONE', 'America/Los_Angeles');
define('LIVE',              0);
define('SITE_ONLINE',       1);
define('AD_HEADER',         0);
define('AD_SIDE',           0);

define('PASSWORD_MINIMUM',  3);
define('MAX_IMAGE_SIZE',    400000000); // 400 MB

define('POINT_BASE',       1000);
define('POINT_FINAL_BASE', 5000);
define('POINT_BASE_MOD',   2500);

// APF
define('POINT_FINAL_BASE_NEW', serialize(array('10' => 5000,'20' => 25000)));

// APF
define('POINT_BASE_MOD_NEW', serialize(array('10' => 2500,'20' => 12500)));

// QP
define('POINT_BASE_NEW', serialize(array('10' => 1000, '20' => 5000)));

define('RANK_SYSTEMS',   serialize(array('QP' => 'Quality Progression', 'AP' => 'Aeyths Point', 'APF' => 'Aeyths Point Flat',)));

//Temporary storage of abbreviations as it will be shifted to the RankSystem Class

define('VALID_IMAGE_FORMATS', serialize(array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP)));

// Game specific settings
include(strtolower(GAME_NAME_1) . '/settings.php');

foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/config/*.php') as $fileName ) {
    if ( $fileName != $_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/config/configuration.php' ) { include $fileName; }
}

foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/lib/*.php') as $fileName ) { include $fileName; }
foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/utils/*.php') as $fileName ) { include $fileName; }
foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/services/*.php') as $fileName ) { include $fileName; }

include dirname(dirname(dirname(__FILE__))) . '/library/PHPMailer/PHPMailerAutoload.php';

$sid = session_id();

ob_start('ob_gzhandler');

if ( !isset($sid) || $sid == "" ) {
    session_start();
    $sid = session_id();
}