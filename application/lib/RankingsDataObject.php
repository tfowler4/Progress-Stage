<?php

/**
 * rankings data object
 */
class RankingsDataObject extends DetailObject {
    protected $_guildId;
    protected $_dungeonId;
    protected $_worldRank;
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
    protected $_points;
    protected $_trend;
    protected $_prevRank;
    protected $_trendImage;
    protected $_pointDiff;
    protected $_qpPoints;
    protected $_apPoints;
    protected $_apfPoints;
    protected $_qpTrend;
    protected $_apTrend;
    protected $_apfTrend;
    protected $_qpRank;
    protected $_apRank;
    protected $_apfRank;
    protected $_qpPrevRank;
    protected $_apPrevRank;
    protected $_apfPrevRank;
    protected $_isContentCleared = false;

    /**
     * constructor
     * 
     * @param array $params [ PDO query object ]
     */
    public function __construct($params, $rankType, $view) {
        $this->_guildId         = $params['guild_id'];
        $guildDetails           = CommonDataContainer::$guildArray[$this->_guildId];
        $this->_name            = $guildDetails->_name;
        $this->_nameLink        = $guildDetails->_nameLink;
        $this->_server          = $guildDetails->_server;
        $this->_serverLink      = $guildDetails->_serverLink;
        $this->_region          = $guildDetails->_region;
        $this->_country         = $guildDetails->_country;
        $this->_specialProgress = $params['special_progress'];
        $this->_progress        = $params['progress'];
        $this->_worldFirst      = $params['world_first'];
        $this->_regionFirst     = $params['region_first'];
        $this->_serverFirst     = $params['server_first'];
        $this->_countryFirst    = $params['country_first'];
        $this->_recentTime      = $params['recent_time'];
        $this->_recentActivity  = $params['recent_activity'];
        $this->_dungeonId       = $params['dungeon_id'];
        $dungeonDetails         = CommonDataContainer::$dungeonArray[$this->_dungeonId];

        if ( $params['complete'] == $dungeonDetails->_numOfEncounters ) {
            $this->_isContentCleared = true;
        }

        // setting all ranking properties (points, trends, ranks, prev ranks) only world for now
        $this->_qpPoints    = $params['qp_points'];
        $this->_apPoints    = $params['ap_points'];
        $this->_apfPoints   = $params['apf_points'];
        $this->_qpTrend     = $params['qp_world_trend'];
        $this->_apTrend     = $params['ap_world_trend'];
        $this->_apfTrend    = $params['apf_world_trend'];
        $this->_qpRank      = $params['qp_world_rank'];
        $this->_apRank      = $params['ap_world_rank'];
        $this->_apfRank     = $params['apf_world_rank'];
        $this->_qpPrevRank  = $params['qp_world_prev_rank'];
        $this->_apPrevRank  = $params['ap_world_prev_rank'];
        $this->_apfPrevRank = $params['apf_world_prev_rank'];

        // set the main properties for object to use when being called in RankingsModel
        $rankType               = strtolower($rankType) . '_';
        $this->_points          = $params[$rankType . 'points'];
        $this->_worldRank       = $this->_rank      = $params[$rankType . 'world_rank'];

        switch ($view) {
            case 'world':
                $this->_rank      = $params[$rankType . 'world_rank'];
                $this->_trend     = $params[$rankType . 'world_trend'];
                $this->_prevRank  = $params[$rankType . 'world_prev_rank'];
                break;
            case 'region':
                $this->_rank      = $params[$rankType . 'region_rank'];
                $this->_trend     = $params[$rankType . 'region_trend'];
                $this->_prevRank  = $params[$rankType . 'region_prev_rank'];
                break;
            case 'server':
                $this->_rank      = $params[$rankType . 'server_rank'];
                $this->_trend     = $params[$rankType . 'server_trend'];
                $this->_prevRank  = $params[$rankType . 'server_prev_rank'];
                break;
            case 'country':
                $this->_rank      = $params[$rankType . 'country_rank'];
                $this->_trend     = $params[$rankType . 'country_trend'];
                $this->_prevRank  = $params[$rankType . 'country_prev_rank'];
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