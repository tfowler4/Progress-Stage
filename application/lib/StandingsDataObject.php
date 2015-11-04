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

    /**
     * constructor
     * 
     * @param array $params [ PDO query object ]
     */
    public function __construct($params) {
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
        $this->_dungeonId       = $params['dungeon_id'];
        $dungeonDetails         = CommonDataContainer::$dungeonArray[$this->_dungeonId];

        if ( $params['complete'] == $dungeonDetails->_numOfEncounters ) {
            $this->_isContentCleared = true;
        }
    }
}