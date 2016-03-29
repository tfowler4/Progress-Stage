<?php

// social media 
define('IMG_TWITTER_LOGO',        '<img class="social-media-icon-lrg" src="' . FOLD_GRAPHICS . 'icon_social_twitter.png"        alt="Follow us on Twitter!">');
define('IMG_TWITTER_SMALL_LOGO',  '<img class="social-media-icon-sml" src="' . FOLD_GRAPHICS . 'icon_social_twitter_small.png"  alt="Follow us on Twitter!">');
define('IMG_FACEBOOK_SMALL_LOGO', '<img class="social-media-icon-sml" src="' . FOLD_GRAPHICS . 'icon_social_facebook_small.png" alt="Follow us on Facebook!">');

// trending arrows
define('IMG_ARROW_TREND_UP_SML',   '<img class="trend-icon-sml" src="' . FOLD_GRAPHICS . 'icon_trend_up.png"         alt="T-Up">');
define('IMG_ARROW_TREND_DOWN_SML', '<img class="trend-icon-sml" src="' . FOLD_GRAPHICS . 'icon_trend_down.png"       alt="T-Down">');
define('IMG_ARROW_TREND_UP_LRG',   '<img class="trend-icon-lrg" src="' . FOLD_GRAPHICS . 'icon_trend_up_large.png"   alt="T-Up">');
define('IMG_ARROW_TREND_DOWN_LRG', '<img class="trend-icon-lrg" src="' . FOLD_GRAPHICS . 'icon_trend_down_large.png" alt="T-Down">');

// ranking medals
define('IMG_MEDAL_GOLD',   '<img class="medal-icon" src="' . FOLD_GRAPHICS . 'icon_medal_gold.png"   alt="1st">');
define('IMG_MEDAL_SILVER', '<img class="medal-icon" src="' . FOLD_GRAPHICS . 'icon_medal_silver.png" alt="2nd">');
define('IMG_MEDAL_BRONZE', '<img class="medal-icon" src="' . FOLD_GRAPHICS . 'icon_medal_bronze.png" alt="3rd">');

// site logos
define('IMG_SITE_LOGO', '<img class="default-logo" src="' . FOLD_SITE_LOGOS . 'guild_default_logo.png" alt="Site Logo">');

// game logos
if ( defined('GAME_NAME_1') ) { define('IMG_GAME_LOGO_1', '<img class="footer-logo" src="' . FOLD_GAME_LOGOS . 'game_logo_1.png" alt="' . GAME_NAME_1 . ' Logo">'); }
if ( defined('GAME_NAME_2') ) { define('IMG_GAME_LOGO_2', '<img class="footer-logo" src="' . FOLD_GAME_LOGOS . 'game_logo_2.png" alt="' . GAME_NAME_2 . ' Logo">'); }
if ( defined('GAME_NAME_3') ) { define('IMG_GAME_LOGO_3', '<img class="footer-logo" src="' . FOLD_GAME_LOGOS . 'game_logo_3.png" alt="' . GAME_NAME_3 . ' Logo">'); }

// company logos
if ( defined('COMPANY_1') ) { define('IMG_COMPANY_LOGO_1', '<img class="footer-logo" src="' . FOLD_GAME_LOGOS . 'company_logo_1.png" alt="' . COMPANY_1 . ' Logo">'); }
if ( defined('COMPANY_2') ) { define('IMG_COMPANY_LOGO_2', '<img class="footer-logo" src="' . FOLD_GAME_LOGOS . 'company_logo_2.png" alt="' . COMPANY_2 . ' Logo">'); }
if ( defined('COMPANY_3') ) { define('IMG_COMPANY_LOGO_3', '<img class="footer-logo" src="' . FOLD_GAME_LOGOS . 'company_logo_3.png" alt="' . COMPANY_3 . ' Logo">'); }

// globol images
define('IMG_TWITCH_LOGO', "<img src='" . FOLD_GLOBAL . "twitch-logo.png' alt='Twitch'>");