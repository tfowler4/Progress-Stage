<?php

/**
 * guild profile page displaying for a specific guild
 */
class GuildModel extends Model {
    protected $_guildId;
    protected $_name;
    protected $_server;
    protected $_guildDetails;
    protected $_pageDetails;
    protected $_activityArray;
    protected $_latestScreenshot = array();
    protected $_guildLogo;
    protected $_recentActivity;
    protected $_raidProgressionTableHeader;
    protected $_activityTimelineTableHeader;
    
    const PANE_PROFILE = array(
            'Date Created'    => '_dateCreated',
            'Server'          => '_serverLink',
            'Country'         => '_countryLink',
            'Faction'         => '_faction',
            'Guild Leader(s)' => '_leader',
            'Website'         => '_websiteLink',
            'Social Media'    => '_socialNetworks',
            'World Firsts'    => '_worldFirst',
            'Region Firsts'   => '_regionFirst',
            'Server Firsts'   => '_serverFirst',
            'Status'          => '_activeStatus'
        );

    const PANE_NAVIGATION = array(
            'Recent Activity'   => '',
            'Raid Progression'  => '',
            'Activity Timeline' => '',
            'Guild Signature' => ''
        );

    const TABLE_HEADER_PROGRESSION = array(
            array('header' => 'Encounter',      'key' => '_encounterName',   'class' => ''),
            array('header' => 'Server',         'key' => '_killServerLink',  'class' => 'hidden-xs'),
            array('header' => 'Date Completed', 'key' => '_datetime',        'class' => 'text-center '),
            array('header' => 'WR',             'key' => '_worldRankImage',  'class' => 'border-left text-center hidden-xs'),
            array('header' => 'RR',             'key' => '_regionRankImage', 'class' => 'text-center hidden-xs'),
            array('header' => 'SR',             'key' => '_serverRankImage', 'class' => 'text-center hidden-xs'),
            array('header' => 'QP',             'key' => '_QP',              'class' => 'border-left text-center hidden-xs hidden-sm'),
            array('header' => 'AP',             'key' => '_AP',              'class' => 'text-center hidden-xs hidden-sm'),
            array('header' => 'APF',            'key' => '_APF',             'class' => 'text-center hidden-xs hidden-sm'),
            array('header' => 'Kill Videos',    'key' => '_videoLink',       'class' => 'border-left text-center'),
            array('header' => 'Screenshot',     'key' => '_screenshotLink',  'class' => 'text-center')
        );

    const TABLE_HEADER_TIMELINE = array(
            array('header' => 'Encounter',      'key' => '_encounterName',   'class' => ''),
            array('header' => 'Server',         'key' => '_killServerLink',  'class' => 'text-center hidden-xs'),
            array('header' => 'Date Completed', 'key' => '_datetime',        'class' => 'text-center'),
            array('header' => 'Dungeon',        'key' => '_dungeon',         'class' => 'border-left text-center hidden-xs'),
            array('header' => 'Tier',           'key' => '_tier',            'class' => 'text-center hidden-xs'),
            array('header' => 'Time Diff',      'key' => '_span',            'class' => 'text-center hidden-sm'),
            array('header' => 'WR',             'key' => '_worldRankImage',  'class' => 'border-left text-center hidden-xs'),
            array('header' => 'RR',             'key' => '_regionRankImage', 'class' => 'text-center hidden-xs'),
            array('header' => 'SR',             'key' => '_serverRankImage', 'class' => 'text-center hidden-xs')
        );

    const PAGE_TITLE = 'Guild Profile';
    const PAGE_NAME  = 'Guild Profile';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        if ( isset($params[1]) && $params[1] == 'sig' ) {
            $paramsArray = array_slice($params, 2);

            $guildSig = new GuildSignature($paramsArray);
            die;
        }

        $this->title = self::PAGE_TITLE;

        $params = explode('-_-', $params[0]);
        
        /*
         * Possible Inputs: /81, /name, /name-_-server
         */
        
        if ( isset($params[0]) && is_numeric($params[0]) ) { 
            $this->_guildId = $params[0]; 
        } else {
            $this->_name    = $params[0];
        }
        
        if ( isset($params[1]) ) { $this->_server = $params[1]; }

        $this->_guildDetails = $this->_getGuildDetails();
        if ( empty($this->_guildDetails) ) { Functions::sendTo404(); }

        $this->_getAllGuildDetails();
        $this->_activityArray = $this->_getActivityTimeline();

        $maxCount = 3;
        foreach ( $this->_activityArray as $encounterId => $encounterDetails ) {
            if ( count($this->_latestScreenshot) == $maxCount ) { break; }
            $this->_latestScreenshot[] = Template::getScreenshot($this->_guildDetails, $encounterDetails, true);
            $this->_recentActivity[]   = $this->_getRecentActivityDetails($encounterDetails); //$encounterDetails;
        }
        

        $this->_raidProgressionTableHeader  = self::TABLE_HEADER_PROGRESSION;
        $this->_activityTimelineTableHeader = self::TABLE_HEADER_TIMELINE;

        //if ( !empty($this->_latestScreenshot) ) {
        //    $this->_recentActivity = $this->_getRecentActivityDetails();
        //}

        $this->_mergeRankDetailsToEncounters();

        $this->title = $this->_guildDetails->_name . ' ' . self::PAGE_NAME;
    }

    /**
     * get guild details details object
     * 
     * @return return mixed [ if no guild is found return null, else Guild Details details object ]
     */
    private function _getGuildDetails() {
        if ( isset($this->_guildId) ) { return CommonDataContainer::$guildArray[$this->_guildId]; }
        if ( isset($this->_name) && !isset($this->_server) ) { return $this->_getDetailsByName($this->_name); }
        if ( isset($this->_name) && isset($this->_server) ) { return $this->_getDetailsByNameServer($this->_name, $this->_server); }
        return null;
    }

    /**
     * get guild details object by guild name
     * 
     * @param  string $guildName [ name of guild ]
     * 
     * @return return mixed [ if no guild is found return null, else Guild Details details object ]
     */
    private function _getDetailsByName($guildName) {
        $guildName = trim(str_replace("_", " ", $guildName));
    
        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( strcasecmp($guildName, $guildDetails->_name) == 0 ) { array_push($guildArray, $guildDetails); 
                return $guildDetails; 
            }
        }
        
        return null;
    }

    /**
     * get guild details object by guild name and server
     * 
     * @param  string $guildName [ name of guild ]
     * @param  string $server    [ name of server ]
     * 
     * @return return mixed [ if no guild is found return null, else Guild Details details object ]
     */
    private function _getDetailsByNameServer($guildName, $server) {
        $guildName = trim(str_replace("_", " ", $guildName));
        
        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( strcasecmp($guildName, $guildDetails->_name) == 0 && strcasecmp($server, $guildDetails->_server) == 0 ) {
                return $guildDetails;
            }
        }
    
        return null;
    }

    /**
     * merge ranking details and standing details into new object
     * 
     * @return void
     */
    private function _mergeRankDetailsToEncounters() {
        foreach( $this->_guildDetails->_encounterDetails as $encounterId => $encounterDetails ) {
            $newEncounterDetails = new stdClass();

            $encounterProperties = $encounterDetails->getProperties();

            foreach ( $encounterProperties as $key => $value ) {
                $newEncounterDetails->$key = $value;
            }

            foreach ( CommonDataContainer::$rankSystemArray as $systemId => $systemDetails ) {
                $systemAbbreviation = $systemDetails->_abbreviation;
                $identifier         = $encounterId . '_' . $systemAbbreviation;

                $newEncounterDetails->{'_' . $systemAbbreviation} = '--';

                if ( isset($this->_guildDetails->_rankDetails->_rankEncounters->$identifier->_points) ) {
                    $newEncounterDetails->{'_' . $systemAbbreviation} = Functions::formatPoints($this->_guildDetails->_rankDetails->_rankEncounters->$identifier->_points);
                }
            }

            $this->_guildDetails->_encounterDetails->$encounterId = $newEncounterDetails;
        }
    }

    /**
     * generate activity line from newest to oldest kill
     * 
     * @return array [ list of kills sorted by time ]
     */
    private function _getActivityTimeline() {
        $returnArray   = array();
        $killTimeArray = array();

        foreach( $this->_guildDetails->_encounterDetails as $encounterDetails ) {
            $strtotime   = $encounterDetails->_strtotime;
            $encounterId = $encounterDetails->_encounterId;

            $killTimeArray[$encounterId] = $strtotime;
        }

        arsort($killTimeArray);

        $currentStrToTime = '';

        foreach( $killTimeArray as $encounterId => $strtotime ) {
            $this->_guildDetails->_encounterDetails->$encounterId->_span = $this->_getKillSpan($currentStrToTime, $strtotime);
            $currentStrToTime = $strtotime;

            $returnArray[$encounterId] = $this->_guildDetails->_encounterDetails->$encounterId;
        }

        return $returnArray;
    }

    /**
     * get time between two kills
     * 
     * @param  string $currentStrToTime [ current unix timestamp ]
     * @param  string $newStrToTime     [ new unix timestamp ]
     * 
     * @return string [ formatted time between kills ]
     */
    private function _getKillSpan($currentStrToTime, $newStrToTime) {
        $returnSpan = '--';

        if ( empty($currentStrToTime) ) {
            return $returnSpan;
        }

        $currentKillDate = date_create();
        $newKillDate     = date_create();

        date_timestamp_set($currentKillDate, $currentStrToTime);
        date_timestamp_set($newKillDate, $newStrToTime);

        $diff  = date_diff($newKillDate, $currentKillDate);
        $result     = $diff->format("%R%a days ago");
        $months     = $diff->m;
        $hours      = $diff->h;
        $days       = $diff->d;
        $minutes    = $diff->i;
        $time       = '';

        if ( $hours > 0 && $days <= 0 && $months <= 0 ) {
            $minutes = number_format($minutes / 60, 0, '', '');
            $time    = '-' . $hours . '.' . $minutes . ' Hours';
        } elseif ( $days > 0 && $months <= 0 ) {
            $hours = number_format($hours / 24, 0, '', '');
            $time  = '-' . $days . '.' . $hours . ' Days';
        } elseif ( $months > 0  ) {
            $numOfDays = number_format(($newStrToTime - $currentStrToTime)  / (60 * 60 * 24), 0, '', '');
            $minutes   = number_format($minutes / 60, 0, '', '');
            $time      = '-' . $numOfDays . '.' . $minutes . ' Days';
        } else {
            $time = '-' . $minutes . ' Minutes';
        }

        $returnSpan = $time;

        return $returnSpan;
    }

    /**
     * get details of the most recently completed encounter
     * 
     * @return array [ encounter details in array format ]
     */
    private function _getRecentActivityDetails($activityDetails) {
        $returnArray      = array();
        //$activityDetails  = $this->_guildDetails->_recentEncounterDetails;
        $encounterId      = $activityDetails->_encounterId;
        $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

        $returnArray['encounterName'] = $encounterDetails->_name;
        $returnArray['dungeon']       = $encounterDetails->_dungeon;
        $returnArray['datetime']      = $activityDetails->_datetime;
        $returnArray['worldRank']     = 'World ' . Functions::convertToOrdinal($activityDetails->_worldRank);
        $returnArray['regionRank']    = $this->_guildDetails->_region . ' ' . Functions::convertToOrdinal($activityDetails->_regionRank);
        $returnArray['serverRank']    = $this->_guildDetails->_server . ' ' . Functions::convertToOrdinal($activityDetails->_serverRank);

        return $returnArray;
    }

    /**
     * generate all encounter standings and rankings information
     *      * 
     * @return void
     */
    public function _getAllGuildDetails() {
        $this->_guildDetails->generateRankDetails('encounters');

        $dbh       = DbFactory::getDbh();
        $dataArray = array();

        $query = $dbh->prepare(sprintf(
            "SELECT kill_id,
                    guild_id,
                    encounter_id,
                    dungeon_id,
                    tier,
                    raid_size,
                    datetime,
                    date,
                    time,
                    time_zone,
                    server,
                    videos,
                    server_rank,
                    region_rank,
                    world_rank,
                    country_rank
               FROM %s
              WHERE guild_id=%d", 
                    DbFactory::TABLE_KILLS, 
                    $this->_guildDetails->_guildId
                ));
        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $encounterId         = $row['encounter_id'];
            $encounterDetails    = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonId           = $encounterDetails->_dungeonId;
            $dungeonDetails      = CommonDataContainer::$dungeonArray[$dungeonId];
            $tierId              = $dungeonDetails->_tier;
            $tierDetails         = CommonDataContainer::$tierArray[$tierId];

            $arr                                      = $this->_guildDetails->_progression;
            $arr['dungeon'][$dungeonId][$encounterId] = $row;
            $arr['encounter'][$encounterId]           = $row;
            $this->_guildDetails->_progression        = $arr;
        }

        $this->_guildDetails->generateEncounterDetails('');
    }

    /**
     * generate page internal hyperlink anchor
     * 
     * @param  string $location [ name of location ]
     * @param  string $class    [ custom css class ]
     * 
     * @return string [ html string containing hyperlink anchor ]
     */
    public function generateInternalAnchor($location, $class) {
        $url       = '#' . $location;
        $url       = strtolower(str_replace(" ", "-", $url)) . '-anchor';
        $hyperlink = '';

        if (!empty($class)) {
            $class = 'class="' . $class . '"';
        }

        $hyperlink = '<a ' . $class . ' href="' . $url . '">' . $location . '</a>';

        return $hyperlink;
    }
}