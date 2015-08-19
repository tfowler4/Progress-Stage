<?php
if ( empty(DOMAIN) ) {
    define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT']);
} else {
    define('ABSOLUTE_PATH', $_SERVER['DOCUMENT_ROOT'] . '/' . DOMAIN);
}

define('FOLD_INDEX',  HOST_NAME . '/');
define('FOLD_APP',    HOST_NAME . '/application');
define('FOLD_PUBLIC', HOST_NAME . '/public');

define('FOLD_SCRIPTS',    ABSOLUTE_PATH . '/scripts/');
define('FOLD_BACKUPS',    ABSOLUTE_PATH . '/data/backups/');
define('FOLD_LOGS',       ABSOLUTE_PATH . '/data/logs/');
define('FOLD_WIDGETS',    ABSOLUTE_PATH . '/public/images/' . strtolower(GAME_NAME_1) . '/widgets/');
define('FOLD_FONTS',      ABSOLUTE_PATH . '/public/fonts/');

// image folder paths
define('FOLD_IMAGES',      FOLD_PUBLIC .      '/images/');
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

define('IMG_TWITTER_LOGO',        "<img style='height:35px; width:35px; vertical-align:middle;'   src='" . FOLD_GRAPHICS . "icon_social_twitter.png'        alt='Follow us on Twitter!'>");
define('IMG_TWITTER_SMALL_LOGO',  "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_social_twitter_small.png'  alt='Follow us on Twitter!'>");
define('IMG_FACEBOOK_SMALL_LOGO', "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_social_facebook_small.png' alt='Follow us on Facebook!'>");
define('IMG_DROPDOWN_ARROW',      "<img style='height:6px; width:9px;'     src='" . FOLD_GRAPHICS . "arrow_down.png'                 alt='Arrow'>");

define('IMG_GAME_ICON_1',           "<img style='height:40px; width:93px;'   src='" . FOLD_GRAPHICS . "icon_game_1.png'                alt='" . GAME_NAME_1 . " Homepage'>");
define('IMG_ARROW_TREND_UP_SML',    "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_trend_up.png'              class='trend-icon' alt='T-Up'>");
define('IMG_ARROW_TREND_DOWN_SML',  "<img style='height:20px; width:20px;'   src='" . FOLD_GRAPHICS . "icon_trend_down.png'            class='trend-icon' alt='T-Down'>");
define('IMG_ARROW_TREND_UP_LRG',   "<img style='height:30px; width:30px;'   src='" . FOLD_GRAPHICS . "icon_trend_up_large.png'        alt='T-Up'>");
define('IMG_ARROW_TREND_DOWN_LRG', "<img style='height:30px; width:30px;'   src='" . FOLD_GRAPHICS . "icon_trend_down_large.png'      alt='T-Down'>");
define('IMG_ICON_NEWS',            "<img style='height:45px; width:45px;'   src='" . FOLD_GRAPHICS . "icon_news.png'                  alt='News'>");
define('IMG_ICON_PATCH',           "<img style='height:45px; width:45px;'   src='" . FOLD_GRAPHICS . "icon_patch.png'                 alt='Patch'>");
define('IMG_MEDAL_GOLD',           "<img style='height:16px; width:16px;'   src='" . FOLD_GRAPHICS . "icon_medal_gold.png'            alt='1st'>");
define('IMG_MEDAL_SILVER',         "<img style='height:16px; width:16px;'   src='" . FOLD_GRAPHICS . "icon_medal_silver.png'          alt='2nd'>");
define('IMG_MEDAL_BRONZE',         "<img style='height:16px; width:16px;'   src='" . FOLD_GRAPHICS . "icon_medal_bronze.png'          alt='3rd'>");
define('IMG_LOADING_SPINNER',      "<img style='height:100px; width:100px;' src='" . FOLD_GRAPHICS . "load_spinner.gif'               alt='Loading'>");
define('IMG_ICON_SEARCH',          "<img style='height:15ppx; width:15px; margin-left:-10px; vertical-align:middle;' src='" . FOLD_GRAPHICS . "icon_search.png'               alt='Search'>");

define('IMG_ARROW_DROPDOWN', "<img style='height:10px; width:10px; vertical-align:middle;'   src='" . FOLD_GRAPHICS . "icon-dropdown.png' alt='Dropdown'>");
define('IMG_ARROW_EXPAND',   "<div style='display:inline-block; position:absolute; right:5px;'><img style='height:8px; width:8px;' src='" . FOLD_GRAPHICS . "icon-expand.png' alt='Expand'></div>");

define('IMG_GAME_LOGO_1', "<img style='width:225px;'  src='" . FOLD_GAME_LOGOS . "game_logo_1.png'    alt='" . GAME_NAME_1 . " Logo'>");
if ( defined('GAME_NAME_2') ) { define('IMG_GAME_LOGO_2', "<img style='width:225px;'  src='" . FOLD_GAME_LOGOS . "game_logo_2.png'    alt='" . GAME_NAME_2 . " Logo'>"); }
if ( defined('GAME_NAME_3') ) { define('IMG_GAME_LOGO_3', "<img style='width:225px;'  src='" . FOLD_GAME_LOGOS . "game_logo_3.png'    alt='" . GAME_NAME_3 . " Logo'>"); }

define('IMG_COMPANY_LOGO_1', "<img style='width:225px;'   src='" . FOLD_GAME_LOGOS . "company_logo_1.png' alt='" . COMPANY_1 . " Logo'>");
if ( defined('COMPANY_2') ) { define('IMG_COMPANY_LOGO_2', "<img style='width:225px;'   src='" . FOLD_GAME_LOGOS . "company_logo_2.png' alt='" . COMPANY_2 . " Logo'>"); }
if ( defined('COMPANY_3') ) { define('IMG_COMPANY_LOGO_3', "<img style='width:225px;'   src='" . FOLD_GAME_LOGOS . "company_logo_3.png' alt='" . COMPANY_3 . " Logo'>"); }

define('IMG_FACTION_EXILES',   "<img style='height:200px; width:200px;'  src='" . FOLD_FACTIONS . "exiles_default.png'   alt='Exiles'>");
define('IMG_FACTION_DOMINION', "<img style='height:200px; width:200px;'  src='" . FOLD_FACTIONS . "dominion_default.png' alt='Dominion'>");

define('IMG_DEFAULT_LOGO', "<img style='height:200px; width:200px;'  src='" . FOLD_GUILD_LOGOS . "tmp/default.png' alt='Default Logo'>");
define('IMG_SITE_LOGO',    "<img style='height:200px; width:200px;'  src='" . FOLD_SITE_LOGOS . "guild_default_logo.png' alt='Site Logo'>");