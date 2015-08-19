<?php

/**
 * encounter details data object for guild
 */
class EncounterDetails extends DetailObject {
    protected $_encounterName;
    protected $_encounterId;
    protected $_dungeon;
    protected $_tier;
    protected $_server;
    protected $_serverLink;
    protected $_date;
    protected $_time;
    protected $_datetime;
    protected $_shorttime;
    protected $_strtotime;
    protected $_timezone;
    protected $_hour;
    protected $_minute;
    protected $_month;
    protected $_day;
    protected $_year;
    protected $_timeDiff;
    protected $_screenshot;
    protected $_screenshotLink = '--';
    protected $_video;
    protected $_videoLink = '--';
    protected $_worldRank = '--';
    protected $_regionRank = '--';
    protected $_serverRank = '--';
    protected $_countryRank = '--';
    protected $_worldRankImage = '--';
    protected $_regionRankImage = '--';
    protected $_serverRankImage = '--';
    protected $_countryRankImage = '--';
    public    $_span;

    /**
     * constructor
     */
    public function __construct(&$params, &$guildDetails, &$dungeonDetails) {
        $this->_encounterId = $params[0];
        $this->_server      = (!empty($params[9]) ? $params[9] : $guildDetails->_server);
        $this->_serverLink  = $guildDetails->_serverLink;
        $this->_date        = $params[1];
        $this->_time        = $params[2];
        $this->_datetime    = Functions::formatDate($params[1] . ' ' . $params[2], 'm/d/Y H:i');
        $this->_shorttime   = Functions::formatDate($params[1] . ' ' . $params[2], 'm/d H:i');
        $this->_strtotime   = strtotime($params[1] . ' ' . $params[2]);
        $this->_timezone    = (!empty($params[3]) ? $params[3] : 'SST'); //Standard Server Time
        $this->_hour        = Functions::formatDate($params[2], 'h');
        $this->_minute      = Functions::formatDate($params[2], 'i');
        $this->_month       = Functions::formatDate($params[1], 'm');
        $this->_day         = Functions::formatDate($params[1], 'd');
        $this->_year        = Functions::formatDate($params[1], 'Y');
        $this->_screenshot  = $guildDetails->_guildId . '-' . $this->_encounterId;
        $this->_video       = $params[4];

        // Add Encounter Specific details from Encounter Class for faster reference
        $this->_encounterName = Functions::generateInternalHyperlink('standings', CommonDataContainer::$encounterArray[$this->_encounterId], 'world', CommonDataContainer::$encounterArray[$this->_encounterId]->_encounterName, '');
        $this->_dungeon       = CommonDataContainer::$encounterArray[$this->_encounterId]->_dungeon;
        $this->_tier          = CommonDataContainer::$encounterArray[$this->_encounterId]->_tier;

        if ( file_exists(strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/screenshots/killshots/' . $guildDetails->_guildId . '-' . $this->_encounterId)) ) {
            $this->_screenshotLink = '<a href="' . FOLD_KILLSHOTS . $guildDetails->_guildId . '-' . $this->_encounterId  . '" rel="lightbox[\'kill_shots\']">View</a>';
        } else {
            $this->_screenshotLink = '--';
        }

        if ( !empty($params[4]) ) { $this->_videoLink        = '<a target="_blank" href="' . $params[4] . '">View</a>'; }
        if ( !empty($params[6]) ) { $this->_serverRank       = $params[5]; }
        if ( !empty($params[7]) ) { $this->_regionRank       = $params[6]; }
        if ( !empty($params[8]) ) { $this->_worldRank        = $params[7]; }
        if ( !empty($params[8]) ) { $this->_countryRank      = $params[8]; }
        if ( !empty($params[6]) ) { $this->_serverRankImage  = Functions::getRankMedal($params[5]); }
        if ( !empty($params[7]) ) { $this->_regionRankImage  = Functions::getRankMedal($params[6]); }
        if ( !empty($params[8]) ) { $this->_worldRankImage   = Functions::getRankMedal($params[7]); }
        if ( !empty($params[8]) ) { $this->_countryRankImage = Functions::getRankMedal($params[8]); }
    
        // Apply EU Time Diff
        if ( $guildDetails->_region == 'EU' && $dungeonDetails->_euTimeDiff > 0 ) {
            $this->_strtotime = strtotime("-".($dungeonDetails->_euTimeDiff + (7*60)) . ' minutes', $this->_strtotime);
            $this->_datetime  = date('m/d/Y H:i', $this->_strtotime);
        }
    }
}