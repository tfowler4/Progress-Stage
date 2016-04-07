<?php

/**
 * standings data object
 */
class StandingsDataObject extends DetailObject {
    protected $_guildId;
    protected $_dungeonId;
    protected $_rank;
    protected $_name;
    protected $_nameLink;
    protected $_region;
    protected $_server;
    protected $_serverLink;
    protected $_country;
    protected $_progress;
    protected $_specialProgress;
    protected $_achievement;
    protected $_worldFirst;
    protected $_regionFirst;
    protected $_serverFirst;
    protected $_countryFirst;
    protected $_recentTime;
    protected $_recentActivity;
    protected $_isContentCleared = false;
    protected $_worldTrend;
    protected $_regionTrend;
    protected $_serverTrend;
    protected $_countryTrend;
    protected $_worldRank;
    protected $_regionRank;
    protected $_serverRank;
    protected $_countryRank;
    protected $_trend;
    protected $_trendImage;

    /**
     * constructor
     * 
     * @param array $params [ PDO query object ]
     */
    public function __construct($params, $view = 'world') {
        $this->_guildId         = $params['guild_id'];
        $guildDetails           = CommonDataContainer::$guildArray[$this->_guildId];
        $this->_name            = $guildDetails->_name;
        $this->_nameLink        = $guildDetails->_nameLink;
        $this->_server          = $guildDetails->_server;
        $this->_serverLink      = $guildDetails->_serverLink;
        $this->_region          = $guildDetails->_region;
        $this->_country         = $guildDetails->_country;
        $this->_progress        = $params['progress'];
        $this->_specialProgress = $params['special_progress'];
        $this->_achievement     = $params['achievement'];
        $this->_worldFirst      = $params['world_first'];
        $this->_regionFirst     = $params['region_first'];
        $this->_serverFirst     = $params['server_first'];
        $this->_countryFirst    = $params['country_first'];
        $this->_recentTime      = $params['recent_time'];
        $this->_recentActivity  = $params['recent_activity'];
        $this->_worldTrend      = $params['world_trend'];
        $this->_regionTrend     = $params['region_trend'];
        $this->_serverTrend     = $params['server_trend'];
        $this->_countryTrend    = $params['country_trend'];
        $this->_worldRank       = $params['world_rank'];
        $this->_regionRank      = $params['region_rank'];
        $this->_serverRank      = $params['server_rank'];
        $this->_countryRank     = $params['country_rank'];
        $this->_dungeonId       = $params['dungeon_id'];
        $dungeonDetails         = CommonDataContainer::$dungeonArray[$this->_dungeonId];

        if ( $params['complete'] == $dungeonDetails->_numOfEncounters ) {
            $this->_isContentCleared = true;
        }

        switch ( $view ) {
            case 'world':
                $this->_trend = $this->_worldTrend;
                break;
            case 'region':
                $this->_trend = $this->_worldTrend;
                break;
            case 'server':
                $this->_trend = $this->_regionTrend;
                break;
            case 'country':
                $this->_trend = $this->_countryTrend;
                break;
        }

        $this->_trendImage = $this->_trend;

        if ( $this->_trend > 0 ) {
            $this->_trendImage = IMG_ARROW_TREND_UP_SML . ' ' . $this->_trend;
        }

        if ( $this->_trend < 0 ) {
            $this->_trendImage = IMG_ARROW_TREND_DOWN_SML . ' ' . $this->_trend;
        }
    }
}