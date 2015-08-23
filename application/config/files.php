<?php
if ( empty(DOMAIN) ) {
    define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT']);
} else {
    define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN);
}

// base folder locations
define('FOLD_INDEX',  HOST_NAME . '/');
define('FOLD_APP',    HOST_NAME . '/application');
define('FOLD_PUBLIC', HOST_NAME . '/public');

// absolute pathing folders where adding/deletes will occur
define('FOLD_SCRIPTS',    ABSOLUTE_PATH . '/scripts/');
define('FOLD_BACKUPS',    ABSOLUTE_PATH . '/data/backups/');
define('FOLD_LOGS',       ABSOLUTE_PATH . '/data/logs/');
define('FOLD_WIDGETS',    ABSOLUTE_PATH . '/public/images/' . strtolower(GAME_NAME_1) . '/widgets/');
define('FOLD_FONTS',      ABSOLUTE_PATH . '/public/fonts/');

// image folder paths
define('FOLD_IMAGES',      FOLD_PUBLIC .      '/images/');
define('FOLD_GLOBAL',      FOLD_IMAGES .      '/global/');
define('FOLD_FLAGS',       FOLD_IMAGES .      '/flags/');
define('FOLD_GAME_IMAGES', FOLD_IMAGES .      strtolower(GAME_NAME_1) . '/');
define('FOLD_FACTIONS',    FOLD_GAME_IMAGES . 'factions/');
define('FOLD_GUILDS',      FOLD_GAME_IMAGES . 'guilds/');
define('FOLD_GUILD_LOGOS', FOLD_GUILDS .      'logos/');
define('FOLD_GRAPHICS',    FOLD_GAME_IMAGES . 'graphics/');
define('FOLD_LOGOS',       FOLD_GAME_IMAGES . 'logos/');
define('FOLD_TWITCH',      FOLD_GAME_IMAGES . 'twitch/');
define('FOLD_SITE_LOGOS',  FOLD_LOGOS .       'site/');
define('FOLD_GAME_LOGOS',  FOLD_LOGOS .       'game/');
define('FOLD_SCREENSHOTS', FOLD_GAME_IMAGES . 'screenshots/');
define('FOLD_KILLSHOTS',   FOLD_SCREENSHOTS . 'killshots/');

// page url paths
define('PAGE_INDEX',           FOLD_INDEX . '');
define('PAGE_NEWS',            FOLD_INDEX . 'news/');
define('PAGE_STANDINGS',       FOLD_INDEX . 'standings/');
define('PAGE_RANKINGS',        FOLD_INDEX . 'rankings/');
define('PAGE_SEARCH',          FOLD_INDEX . 'search/');
define('PAGE_RESET_PASSWORD',  FOLD_INDEX . 'reset/');
define('PAGE_REGISTER',        FOLD_INDEX . 'register/');
define('PAGE_LOGIN',           FOLD_INDEX . 'login/');
define('PAGE_LOGOUT',          FOLD_INDEX . 'logout/');
define('PAGE_SERVERS',         FOLD_INDEX . 'servers/');
define('PAGE_USER_PANEL',      FOLD_INDEX . 'userpanel/');
define('PAGE_GUILD',           FOLD_INDEX . 'guild/');
define('PAGE_PRIVACY',         FOLD_INDEX . 'privacypolicy/');
define('PAGE_CONTACTUS',       FOLD_INDEX . 'contactus/');
define('PAGE_TOS',             FOLD_INDEX . 'termsofservice/');
define('PAGE_HOW',             FOLD_INDEX . 'howto/');
define('PAGE_QUICK',           FOLD_INDEX . 'quicksubmission/');
define('PAGE_SERVER_RANKINGS', FOLD_INDEX . 'server_rankings/');
define('PAGE_DIRECTORY',       FOLD_INDEX . 'guilddirectory/');
define('PAGE_ADMIN',           FOLD_INDEX . 'administrator/');