<?php
if ( empty(DOMAIN) ) {
    define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT']);
} else {
    define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN);
}

define('FOLD_INDEX',  HOST_NAME . '/');
define('FOLD_APP',    HOST_NAME . '/application');
define('FOLD_PUBLIC', HOST_NAME . '/public');

define('FOLD_CONFIG',   FOLD_APP . '/config/');
define('FOLD_MODELS',   FOLD_APP . '/models/');

define('FOLD_CSS',        FOLD_PUBLIC . '/css/');
define('FOLD_ERRORS',     FOLD_PUBLIC . '/errors/');
define('FOLD_JAVASCRIPT', FOLD_PUBLIC . '/js/');
define('FOLD_TWITTER',    FOLD_PUBLIC . '/twitter/');
define('FOLD_SCRIPTS',    ABSOLUTE_PATH . '/scripts/');
define('FOLD_BACKUPS',    ABSOLUTE_PATH . '/data/backups/');
define('FOLD_LOGS',       ABSOLUTE_PATH . '/data/logs/');
define('FOLD_IMAGES',     FOLD_PUBLIC . '/images/' . strtolower(GAME_NAME_1) . '/');
define('FOLD_FLAGS',      FOLD_PUBLIC . '/images/flags/'); // Shared Image Folder due to shared data
define('FOLD_WIDGETS',    ABSOLUTE_PATH . '/public/images/' . strtolower(GAME_NAME_1) . '/widgets/');
define('FOLD_FONTS',      ABSOLUTE_PATH . '/public/fonts/');

define('FOLD_ADMIN',           FOLD_MODELS . 'admin/');
define('FOLD_SEARCH',          FOLD_MODELS . 'search/');
define('FOLD_REGISTER',        FOLD_MODELS . 'register/');
define('FOLD_LOGIN',           FOLD_MODELS . 'login/');
define('FOLD_LOGOUT',          FOLD_MODELS . 'logout/');
define('FOLD_FAQ',             FOLD_MODELS . 'faq/');
define('FOLD_SERVERS',         FOLD_MODELS . 'servers/');
define('FOLD_FORGOT',          FOLD_MODELS . 'forgot/');
define('FOLD_USER_PANEL',      FOLD_MODELS . 'userpanel/');
define('FOLD_GUILD',           FOLD_MODELS . 'guild/');
define('FOLD_NEWS',            FOLD_MODELS . 'news/');
define('FOLD_STANDINGS',       FOLD_MODELS . 'standings/');
define('FOLD_RANKINGS',        FOLD_MODELS . 'rankings/');
define('FOLD_TOS',             FOLD_MODELS . 'tos/');
define('FOLD_PRIVACY',         FOLD_MODELS . 'privacy/');
define('FOLD_CONTACT',         FOLD_MODELS . 'contact/');
define('FOLD_HOWTO',           FOLD_MODELS . 'how_it_works/');
define('FOLD_CRON',            FOLD_MODELS . 'cron/');
define('FOLD_QUICK',           FOLD_MODELS . 'quick/');
define('FOLD_SERVER_RANKINGS', FOLD_MODELS . 'server_rankings/');

define('FOLD_FACTIONS',    FOLD_IMAGES .      'factions/');
define('FOLD_GUILDS',      FOLD_IMAGES .      'guilds/');
define('FOLD_GUILD_LOGOS', FOLD_GUILDS .      'logos/');
define('FOLD_GRAPHICS',    FOLD_IMAGES .      'graphics/');
define('FOLD_LOGOS',       FOLD_IMAGES .      'logos/');
define('FOLD_TWITCH',      FOLD_IMAGES .      'twitch/');
define('FOLD_SITE_LOGOS',  FOLD_LOGOS .       'site/');
define('FOLD_GAME_LOGOS',  FOLD_LOGOS .       'game/');
define('FOLD_SCREENSHOTS', FOLD_IMAGES .      'screenshots/');
define('FOLD_KILLSHOTS',   FOLD_SCREENSHOTS . 'killshots/');

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
define('PAGE_ADMIN',           FOLD_INDEX . '/administrator');

$GLOBALS['images']['twitter']          = "<img style='height:35px; width:35px; vertical-align:middle;'   src='" . FOLD_GRAPHICS . "icon_social_twitter.png'        alt='Follow us on Twitter!'>";
$GLOBALS['images']['twitter_small']    = "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_social_twitter_small.png'  alt='Follow us on Twitter!'>";
$GLOBALS['images']['arrow']            = "<img style='height:6px; width:9px;'     src='" . FOLD_GRAPHICS . "arrow_down.png'                 alt='Arrow'>";
$GLOBALS['images']['twitter_small']    = "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_social_twitter_small.png'  alt='Follow us on Twitter!'>";
$GLOBALS['images']['facebook_small']   = "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_social_facebook_small.png' alt='Follow us on Facebook!'>";
$GLOBALS['images']['google_small']     = "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_social_google_small.png'   alt='Follow us on Google+!'>";
$GLOBALS['images']['icon_game_1']      = "<img style='height:40px; width:93px;'   src='" . FOLD_GRAPHICS . "icon_game_1.png'                alt='" . GAME_NAME_1 . " Homepage'>";
$GLOBALS['images']['trend_up']         = "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_trend_up.png'              class='trend-icon' alt='T-Up'>";
$GLOBALS['images']['trend_down']       = "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_trend_down.png'            class='trend-icon' alt='T-Down'>";
$GLOBALS['images']['trend_up_large']   = "<img style='height:30px; width:30px;'   src='" . FOLD_GRAPHICS . "icon_trend_up_large.png'        alt='T-Up'>";
$GLOBALS['images']['trend_down_large'] = "<img style='height:30px; width:30px;'   src='" . FOLD_GRAPHICS . "icon_trend_down_large.png'      alt='T-Down'>";
$GLOBALS['images']['icon_news']        = "<img style='height:45px; width:45px;'   src='" . FOLD_GRAPHICS . "icon_news.png'                  alt='News'>";
$GLOBALS['images']['icon_patch']       = "<img style='height:45px; width:45px;'   src='" . FOLD_GRAPHICS . "icon_patch.png'                 alt='Patch'>";
$GLOBALS['images']['medal_gold']       = "<img style='height:16px; width:16px;'   src='" . FOLD_GRAPHICS . "icon_medal_gold.png'            alt='1st'>";
$GLOBALS['images']['medal_silver']     = "<img style='height:16px; width:16px;'   src='" . FOLD_GRAPHICS . "icon_medal_silver.png'          alt='2nd'>";
$GLOBALS['images']['medal_bronze']     = "<img style='height:16px; width:16px;'   src='" . FOLD_GRAPHICS . "icon_medal_bronze.png'          alt='3rd'>";
$GLOBALS['images']['loading']          = "<img style='height:100px; width:100px;' src='" . FOLD_GRAPHICS . "load_spinner.gif'               alt='Loading'>";
$GLOBALS['images']['search']           = "<img style='height:15ppx; width:15px; margin-left:-10px; vertical-align:middle;' src='" . FOLD_GRAPHICS . "icon_search.png'               alt='Search'>";

$GLOBALS['images']['icon-dropdown']    = "<img style='height:10px; width:10px; vertical-align:middle;'   src='" . FOLD_GRAPHICS . "icon-dropdown.png' alt='Dropdown'>";
$GLOBALS['images']['icon-expand']      = "<div style='display:inline-block; position:absolute; right:5px;''><img style='height:8px; width:8px;' src='" . FOLD_GRAPHICS . "icon-expand.png' alt='Expand'></div>";

$GLOBALS['images']['logo_game_1'] = "<img style='width:225px;'  src='" . FOLD_GAME_LOGOS . "game_logo_1.png'    alt='" . GAME_NAME_1 . " Logo'>";
if ( defined('GAME_NAME_2') ) { $GLOBALS['images']['logo_game_2'] = "<img style='width:225px;'  src='" . FOLD_GAME_LOGOS . "game_logo_2.png'    alt='" . GAME_NAME_2 . " Logo'>"; }
if ( defined('GAME_NAME_3') ) { $GLOBALS['images']['logo_game_3'] = "<img style='width:225px;'  src='" . FOLD_GAME_LOGOS . "game_logo_3.png'    alt='" . GAME_NAME_3 . " Logo'>"; }

$GLOBALS['images']['logo_company_1'] = "<img style='width:225px;'   src='" . FOLD_GAME_LOGOS . "company_logo_1.png' alt='" . COMPANY_1 . " Logo'>";
if ( defined('COMPANY_2') ) { $GLOBALS['images']['logo_company_2'] = "<img style='width:225px;'   src='" . FOLD_GAME_LOGOS . "company_logo_2.png' alt='" . COMPANY_2 . " Logo'>"; }
if ( defined('COMPANY_3') ) { $GLOBALS['images']['logo_company_3'] = "<img style='width:225px;'   src='" . FOLD_GAME_LOGOS . "company_logo_3.png' alt='" . COMPANY_3 . " Logo'>"; }

$GLOBALS['images']['exiles']   = "<img style='height:200px; width:200px;'  src='" . FOLD_FACTIONS . "exiles_default.png'   alt='Exiles'>";
$GLOBALS['images']['dominion'] = "<img style='height:200px; width:200px;'  src='" . FOLD_FACTIONS . "dominion_default.png' alt='Dominion'>";

$GLOBALS['images']['default_logo']  = "<img style='height:200px; width:200px;'  src='" . FOLD_GUILD_LOGOS . "tmp/default.png' alt='Default Logo'>";
$GLOBALS['images']['site_logo']     = "<img style='height:200px; width:200px;'  src='" . FOLD_SITE_LOGOS . "guild_default_logo.png' alt='Site Logo'>";
$GLOBALS['images']['banner_header'] = "<img style='height:116px; width:284px;'  src='" . FOLD_SITE_LOGOS . "site_logo_1.png' alt='" . GAME_NAME_1 . " Progress'>";