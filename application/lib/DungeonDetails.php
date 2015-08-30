<?php

/**
 * dungeon details detail object
 */
class DungeonDetails extends DetailObject {
    protected $_rank;
    protected $_guildId;
    protected $_name;
    protected $_nameLink;
    protected $_server;
    protected $_serverLink;
    protected $_country;
    protected $_countryImage;
    protected $_countryLink;
    protected $_complete = 0;
    protected $_standing;
    protected $_hardModeComplete = 0;
    protected $_hardModeStanding;
    protected $_conqeuror = 'No';
    protected $_recentActivity;
    protected $_recentTime;
    protected $_recentEncounterDetails;
    protected $_worldFirst = 0;
    protected $_regionFirst = 0;
    protected $_serverFirst = 0;
    protected $_trend;
    protected $_prevRank;
    protected $_pointDiff;
    protected $_qp;
    protected $_ap;
    protected $_apf;

    /**
     * constructor
     */
    public function __construct(&$dungeonDetails, &$guildDetails) {
        $this->_standing         = 0 . '/' . $dungeonDetails->_numOfEncounters;
        $this->_hardModeStanding = 0 . '/' . $dungeonDetails->_numOfSpecialEncounters;

        $this->_guildId      = $guildDetails->_guildId;
        $this->_name         = $guildDetails->_name;
        $this->_nameLink     = $guildDetails->_nameLink;
        $this->_server       = $guildDetails->_server;
        $this->_serverLink   = $guildDetails->_serverLink;
        $this->_country      = $guildDetails->_country;
        $this->_countryImage = $guildDetails->_countryImage;
        $this->_countryLink  = $guildDetails->_countryLink;
    }


    /**
     * get the difference in unix time between two time values
     * 
     * @param  integer $currentTime [ starting unix time value ]
     * @param  integer $newTime     [ new unix time value ]
     * 
     * @return void
     */
    public function getTimeDiff($currentTime, $newTime) {
        $timeDiff           = $newTime - $currentTime;
        $this->_timeDiff    = Functions::convertToDiffDaysHoursMins($timeDiff);

        if ( $currentTime == 0 ) { 
            $this->_timeDiff = '--'; 
        }
    }

}