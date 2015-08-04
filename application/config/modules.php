<?php

define('MODULE_NEWS_SET',      1);
define('MODULE_STANDINGS_SET', 1);
define('MODULE_RANKINGS_SET',  1);
define('MODULE_SERVERS_SET',   1);
define('MODULE_HOWTO_SET',     1);
define('MODULE_GUILD_SET',     1);
define('MODULE_SEARCH_SET',    1);
define('MODULE_REGISTER_SET',  1);
define('MODULE_TOS_SET',       1);
define('MODULE_PRIVACY_SET',   1);
define('MODULE_QUICKSUB_SET',  1);
define('MODULE_CONTACT_SET',   1);
define('MODULE_USERPANEL_SET', 1);
define('MODULE_LOGIN_SET',     1);
define('MODULE_LOGOUT_SET',    1);

$GLOBALS['server_rankings']['header_stats']["Server"]           = "name";
$GLOBALS['server_rankings']['header_stats']["Guilds"]           = "guilds";
$GLOBALS['server_rankings']['header_stats']["Top 25 WW"]        = "top_25";
$GLOBALS['server_rankings']['header_stats']["Top 50 WW"]        = "top_50";
$GLOBALS['server_rankings']['header_stats']["Region Firsts"]    = "num_of_rf";
$GLOBALS['server_rankings']['header_stats']["World Firsts"]     = "num_of_wf";
$GLOBALS['server_rankings']['header_stats']["Total Kills"]      = "num_of_kills";

$GLOBALS['guilddirectory']['header_listing']['Guild']           = '_nameLink';
$GLOBALS['guilddirectory']['header_listing']['Server']          = '_serverLink';
//$GLOBALS['guilddirectory']['header_listing']['Date Created']    = '_dateCreated';
$GLOBALS['guilddirectory']['header_listing']['Guild Leader(s)'] = '_leader';
$GLOBALS['guilddirectory']['header_listing']['Type']            = '_guildType';
$GLOBALS['guilddirectory']['header_listing']['Raid Schedule']   = '_schedule';
$GLOBALS['guilddirectory']['header_listing']['Website']         = '_website';
$GLOBALS['guilddirectory']['header_listing']['Social Networks'] = '_socialNetworks';
$GLOBALS['guilddirectory']['header_listing']['Recent Activity'] = '_recentActivity';

$GLOBALS['guilddirectory']['pane_data']['Total Number of Guilds']   = 'numOfGuilds';
$GLOBALS['guilddirectory']['pane_data']['North American Guilds']    = 'numOfNAGuilds';
$GLOBALS['guilddirectory']['pane_data']['European Guilds']          = 'numOfEUGuilds';

$GLOBALS['user']['guild_details']['Date Created'] = "date_created";
$GLOBALS['user']['guild_details']['Server']       = "server";
$GLOBALS['user']['guild_details']['Country']      = "country";
$GLOBALS['user']['guild_details']['Faction']      = "faction";

$GLOBALS['user']['account_details']['Username']     = "username";
$GLOBALS['user']['account_details']['Email']        = "email";
$GLOBALS['user']['account_details']['Date Created'] = "date_joined";

$GLOBALS['user']['header_progression']['Date Completed'] = "datetime";
$GLOBALS['user']['header_progression']['WR']             = "world_rank";
$GLOBALS['user']['header_progression']['RR']             = "region_rank";
$GLOBALS['user']['header_progression']['SR']             = "server_rank";
$GLOBALS['user']['header_progression']['Kill Video']     = "video";
$GLOBALS['user']['header_progression']['Screenshot']     = "screenshot";

$GLOBALS['user']['message']['new_guild']       = "To create a new guild, click on the 'Create New Guild' button to begin the process!";
$GLOBALS['user']['message']['enroll_default']  = "Your guild is currently not enrolled in our guild raid progression system. Enrolling will allow your guild to be tracked and ranked along side fellow guilds.";
$GLOBALS['user']['message']['enroll_success']  = "Congratulations! Your guild has successfully been enrolled into our raid progression tracker! Use the 'Submit Kills' button to start listing your kills!";
$GLOBALS['user']['message']['disband_confirm'] = "Are you sure you want to disband your guild?";

$GLOBALS['contact']['type'][0] = "General Feedback";
$GLOBALS['contact']['type'][1] = "Guild Details Request";
$GLOBALS['contact']['type'][2] = "UI Layout";
$GLOBALS['contact']['type'][3] = "Account Problem";
$GLOBALS['contact']['type'][4] = "Submission Errors";
$GLOBALS['contact']['type'][5] = "Feature Request";
$GLOBALS['contact']['type'][6] = "Specific Problems";