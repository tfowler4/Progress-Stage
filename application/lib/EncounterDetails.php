<?php

/**
 * encounter details detail object
 */
class EncounterDetails extends DetailObject {
    protected $_rank;
    protected $_guildId;
    protected $_name;
    protected $_nameLink;
    protected $_encounterName;
    protected $_encounterId;
    protected $_dungeon;
    protected $_dungeonId;
    protected $_raidSize;
    protected $_tier;
    protected $_server;
    protected $_serverLink;
    protected $_killServer;
    protected $_killServerLink;
    protected $_country;
    protected $_countryImage;
    protected $_countryLink;
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
    protected $_hasVideo;
    protected $_videos = array();
    protected $_videoLink = '--';
    protected $_worldRank;
    protected $_regionRank;
    protected $_serverRank;
    protected $_countryRank;
    protected $_worldRankImage = '--';
    protected $_regionRankImage = '--';
    protected $_serverRankImage = '--';
    protected $_countryRankImage = '--';
    public    $_span;

    /**
     * constructor
     */
    public function __construct(&$params, &$guildDetails, &$dungeonDetails) {
        $this->_encounterId    = $params['encounter_id'];
        $this->_killServer     = (!empty($params['server']) ? $params['server'] : $guildDetails->_server);
        $this->_date           = $params['date'];
        $this->_time           = $params['time'];
        $this->_datetime       = Functions::formatDate($this->_date . ' ' . $this->_time, 'm/d/Y H:i');
        $this->_shorttime      = Functions::formatDate($this->_date . ' ' . $this->_time, 'm/d H:i');
        $this->_strtotime      = strtotime($this->_date . ' ' . $this->_time);
        $this->_timezone       = (!empty($params['time_zone']) ? $params['time_zone'] : 'SST'); //Standard Server Time
        $this->_hour           = Functions::formatDate($this->_time, 'h');
        $this->_minute         = Functions::formatDate($this->_time, 'i');
        $this->_month          = Functions::formatDate($this->_date, 'm');
        $this->_day            = Functions::formatDate($this->_date, 'd');
        $this->_year           = Functions::formatDate($this->_date, 'Y');
        $this->_screenshot     = $guildDetails->_guildId . '-' . $this->_encounterId;

        $serverDetails  = CommonDataContainer::$serverArray[$this->_killServer];
        $this->_killServerLink = $serverDetails->_nameLink;

        if ( isset($params['videos']) && $params['videos'] > 0 ) {
            $this->_hasVideos = true;
            $this->_videos    = $this->getEncounterVideos($this->_encounterId, $guildDetails->_guildId);
            $this->_videoLink = '<a class="video-activator clickable" data-guild="' . $guildDetails->_guildId . '" data-encounter="' . $this->_encounterId . '">View</a>';
        } else {

        }

        // Add Encounter Specific details from Encounter Class for faster reference
        $this->_encounterName = Functions::generateInternalHyperlink('standings', CommonDataContainer::$encounterArray[$this->_encounterId], 'world', CommonDataContainer::$encounterArray[$this->_encounterId]->_encounterName, '');
        $this->_dungeon       = CommonDataContainer::$encounterArray[$this->_encounterId]->_dungeon;
        $this->_dungeonId     = CommonDataContainer::$encounterArray[$this->_encounterId]->_dungeonId;
        $this->_tier          = CommonDataContainer::$encounterArray[$this->_encounterId]->_tier;
        $this->_raidSize      = CommonDataContainer::$encounterArray[$this->_encounterId]->_raidSize;

        if ( file_exists(strtolower(ABS_FOLD_KILLSHOTS . $guildDetails->_guildId . '-' . $this->_encounterId)) ) {
            $this->_screenshotLink = '<a href="' . FOLD_KILLSHOTS . $guildDetails->_guildId . '-' . $this->_encounterId  . '" rel="lightbox[\'kill_shots\']">View</a>';
        } else {
            $this->_screenshotLink = '--';
        }

        $this->_serverRank       = $params['server_rank'];
        $this->_regionRank       = $params['region_rank'];
        $this->_worldRank        = $params['world_rank'];
        $this->_countryRank      = $params['country_rank'];
        $this->_serverRankImage  = Functions::getRankMedal($this->_serverRank);
        $this->_regionRankImage  = Functions::getRankMedal($this->_regionRank);
        $this->_worldRankImage   = Functions::getRankMedal($this->_worldRank);
        $this->_countryRankImage = Functions::getRankMedal($this->_countryRank);

        // Apply EU Time Diff
        if ( $guildDetails->_region == 'EU' ) {
            $this->_strtotime  = strtotime("-". EU_TIME_DIFF . ' minutes', $this->_strtotime);
            $this->_datetime   = date('m/d/Y H:i', $this->_strtotime);
            $this->_shorttime  = date('m/d H:i', $this->_strtotime);
        }

        if ( $guildDetails->_region == 'EU' && $dungeonDetails->_euTimeDiff > 0 ) {
            $this->_strtotime = strtotime("-". ($dungeonDetails->_euTimeDiff) . ' minutes', $this->_strtotime);
            $this->_datetime  = date('m/d/Y H:i', $this->_strtotime);
            $this->_shorttime  = date('m/d H:i', $this->_strtotime);
        }

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
        $timeDiff        = $newTime - $currentTime;
        $this->_timeDiff = Functions::convertToDiffDaysHoursMins($timeDiff);

        if ( $currentTime == 0 ) { 
            $this->_timeDiff = '--'; 
        }
    }


    /**
     * get the difference in point amounts between two point values
     * 
     * @param  double $currentPoints [ starting points value ]
     * @param  double $newPoints     [ new points value ]
     * 
     * @return void
     */
    public function getPointDiff($currentPoints, $newPoints) {
        $pointDiff        = $newPoints - $currentPoints;
        $this->_pointDiff = Functions::formatPoints($pointDiff);

        if ( $currentPoints == 0 ) { 
            $this->_pointDiff = '--'; 
        }
    }

    public function getEncounterVideos($encounterId, $guildId) {
        $dbh        = DbFactory::getDbh();
        $videoArray = array();

        $query = $dbh->prepare(sprintf(
            "SELECT video_id,
                    guild_id,
                    encounter_id,
                    url,
                    type,
                    notes
               FROM %s
              WHERE guild_id = %d
                AND encounter_id = %d", 
                    DbFactory::TABLE_VIDEOS, 
                    $guildId,
                    $encounterId
                ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $videoArray[$row['video_id']] = new VideoDetails($row);
        }

        return $videoArray;
    }
}