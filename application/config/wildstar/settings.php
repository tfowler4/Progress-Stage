<?php
// WildStar
define('SITE_TITLE',           'WildStar Progress');
define('SITE_TITLE_SHORT',     'WildStar Progress');
define('GAME_URL',             'http://www.wildstar-online.com');
define('META_AUTHOR',          "Terry 'Raive' Fowler");
define('META_KEYWORDS',        "WildStar, WildStar raiding, WildStar progress, progress, raiding, progression, tracker, tracking, rank, ranking, top 25, guild");
define('META_DESCRIPTION',     "WildStar's #1 Resource for raid progression tracking.");
define('LATEST_TIER',          1);
define('EU_TIME_DIFF',         0); // 8 *3600 = 28800
define('NA_PATCH_TIME',        'N/A (Inconsistant)'); //"UTC -8 Hours (PST)"
define('EU_PATCH_TIME',        'N/A (Inconsistant)'); //"UTC +0 Hours (GMT)"
define('FREEZE_KILL_COUNT',    0);
define('FREEZE_KILL_DATE',     0);
define('BASE_POINT_VALUE',     1000);
define('UPDATE_FREQ',          30);
define('POINT_SYSTEM_DEFAULT', 'QP');
define('RELEASE_YEAR',         2014);
define('AD_HEADER',            1);
define('AD_SIDEBAR',           1);
define('REQUIRE_SCREENSHOT',   1);
define('REQUIRE_ENCOUNTERS',   1);
define('EMAIL_ADMIN',          'administrator@wildstar-progress.com');
define('COMPANY_1',            'Carbine Studios');
define('COMPANY_2',            'NCSoft');
define('LINK_COMPANY_1',       'http://www.carbinestudios.com');
define('LINK_COMPANY_2',       'http://www.ncsoft.com');
define('LINK_GAME_1',          'http://www.wildstar-online.com');
define('LINK_TWITTER',         'http://twitter.com/WildstarProgres');
define('COPYRIGHT',            '&copy; ' . RELEASE_YEAR . ' ' . SITE_TITLE . ' - All Rights Reserved.');

define('FACTIONS',   serialize(array('Exiles', 'Dominion')));
define('RAID_SIZES', serialize(array('40', '20')));

define('GOOGLE_ANALYTICS', "(function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
                            (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
                            m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
                            })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
                            ga('create', 'UA-39972522-3', 'wildstar-progress.com');
                            ga('send', 'pageview');");

// Twitter
define('TWITTER_KEY',          'RXbt89WmtcSMxxoBQvXxw');
define('TWITTER_SECRET',       'sEY1zbMS28mMPBWNVpkSciKJdTeEiAyibpeIyZenA');
define('TWITTER_TOKEN',        '1557564476-bXMkncRWTTqGPPULMmL0i9NLP9GPS05Q7LTgJQc');
define('TWITTER_TOKEN_SECRET', 'zsBJr409q64SHCG5kw5W60G63T7GpLrUiX4oH12YR2z8f');

// Register Questions
define('REGISTER_QUESTIONS', serialize(array('What are the two factions? (Exile & ?????)', 'Which company developed WildStar? (***** Studios)')));
define('REGISTER_ANSWERS',   serialize(array('dominion', 'carbine')));

// DB Settings
define('DB_HOST', 'localhost');
define('DB_NAME', 'vgtrin5_wildstar_live');
define('DB_USER', 'vgtrin5_wildstar');
define('DB_PASS', 'WildstarTrinity74108520!');