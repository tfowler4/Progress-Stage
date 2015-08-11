<?php
date_default_timezone_set('America/Los_Angeles');

// Global Settings
// http://www.topofrift.com
// http://www.wildstar-progress.com
// http://stage.trinityguild.org
// http://localhost/stage

// Supported Games
define('GAME_RIFT',     'Rift');
define('GAME_WILDSTAR', 'WildStar');
define('GAME_NAME_1',   GAME_WILDSTAR);
define('LIVE',          0);

if ( strpos($_SERVER['DOCUMENT_ROOT'], '/htdocs') !== FALSE ) { // Local Machine
    if ( GAME_NAME_1 == GAME_RIFT && LIVE == 1 ) { define('HOST_NAME', 'http://' . getHostByName(getHostName()) . '/site-rift'); define('DOMAIN', 'site-rift'); }
    if ( GAME_NAME_1 == GAME_WILDSTAR && LIVE == 1 ) { define('HOST_NAME', 'http://' . getHostByName(getHostName()) . '/site-wildstar'); define('DOMAIN', 'site-wildstar'); }
    if ( !empty(GAME_NAME_1) && LIVE == 0 ) { define('HOST_NAME', 'http://' . getHostByName(getHostName()) . '/stage'); define('DOMAIN', 'stage'); }
} elseif ( strpos($_SERVER['DOCUMENT_ROOT'], '/public_html/') !== FALSE ) { // Webserver
    if ( GAME_NAME_1 == GAME_RIFT && LIVE == 1 ) { define('HOST_NAME', 'http://www.topofrift.com'); }
    if ( GAME_NAME_1 == GAME_WILDSTAR && LIVE == 1 ) { define('HOST_NAME', 'http://www.wildstar-progress.com'); }
    if ( !empty(GAME_NAME_1) && LIVE == 0 ) { define('HOST_NAME', 'http://stage.trinityguild.org'); }

    define('WEBSERVER', 1);
    define('DOMAIN',    '');
}

define('DEFAULT_TIME_ZONE', 'America/Los_Angeles');
define('SITE_ONLINE',       1);
define('PASSWORD_MINIMUM',  3);
define('MAX_IMAGE_SIZE',    400000000); // 400 MB
define('POINT_BASE',       1000); // QP, AP
define('POINT_FINAL_BASE', 5000); // AP
define('POINT_BASE_MOD',   2500); // APF

define('RANK_SYSTEMS',   serialize(array('QP' => 'Quality Progression', 'AP' => 'Aeyths Point', 'APF' => 'Aeyths Point Flat')));

define('VALID_IMAGE_FORMATS', serialize(array(IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG, IMAGETYPE_BMP)));

// FTP
define('FTP_HOST',      'biz163.inmotionhosting.com');
define('FTP_USER',      'vgtrin5');
define('FTP_PASSWORD',  'Vannary74108520');

// Game specific settings
include(strtolower(GAME_NAME_1) . '/settings.php');

foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/config/*.php') as $fileName ) {
    if ( $fileName != $_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/config/configuration.php' ) { include $fileName; }
}

// Twitter
include ABSOLUTE_PATH . '/library/twitter/codebird-php-master/src/codebird.php';

// Bitly
define('BITLY_TOKEN', '14b2b2e1c7525700db1573084e1a64ee45fb33da');
include ABSOLUTE_PATH . '/library/BitlyPHP/bitly.php';

// PHPMailer
include ABSOLUTE_PATH . '/library/PHPMailer/PHPMailerAutoload.php';

foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/lib/*.php') as $fileName ) { include $fileName; }
foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/utils/*.php') as $fileName ) { include $fileName; }
foreach ( glob($_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN . '/application/services/*.php') as $fileName ) { include $fileName; }

// Begin Compression
ob_start('ob_gzhandler');

// Begin Session
if ( session_id() == '' || !isset($_SESSION) ) {
    session_start();
}