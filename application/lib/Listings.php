<?php

/**
 * class to help create and sort a listings of guilds based on
 * standings or rankings
 */
class Listings extends DataObject {
    protected $_topGuildsArray = array();
    protected $_guildArray = array();
    protected $_listType;
    protected $_view;
    protected $_raidSize;
    protected $_tier;
    protected $_tierRaidSize;
    protected $_dungeon;
    protected $_encounter;
    protected $_identifier;
    protected $_dataType;
    protected $_dataDetails;
    protected $_tableHeader;
    protected $_title;
    protected $_isSpreadsheet;
    protected $_tierDetails;

    // RANKINGS
    protected $_rankType;
    protected $_rankingsType;
    protected $_standingsType;
    protected $_standingsId;
    protected $_rankingId;

    // SERVERS
    protected $_serverDetails;
    protected $_dungeonGuildArray;

    public $listArray;

    const PAGE_NAME = 'Standings';

    const TABLE_HEADER_STANDINGS_DUNGEON = array(
            'Rank'            => '_rank',
            'Guild'           => '_nameLink',
            'Server'          => '_serverLink',
            'Progress'        => '_standing',
            'Hard Modes'      => '_hardModeStanding',
            'Conqueror'       => '_conqeuror',
            'WF'              => '_worldFirst',
            'RF'              => '_regionFirst',
            'SF'              => '_serverFirst',
            'Recent Activity' => '_recentActivity'
        );

    const TABLE_HEADER_STANDINGS_ENCOUNTER = array(
            'Rank'            => '_rank',
            'Guild'           => '_nameLink',
            'Server'          => '_serverLink',
            'Date Completed'  => '_datetime',
            'Time Difference' => '_timeDiff',
            'WR'              => '_worldRankImage',
            'RR'              => '_regionRankImage',
            'SR'              => '_serverRankImage',
            'Kill Video'      => '_videoLink',
            'Screenshot'      => '_screenshotLink'
        );

    const TABLE_HEADER_RANKINGS_DEFAULT = array(
            'Rank'          => '_rank',
            'Guild'         => '_nameLink',
            'Server'        => '_serverLink',
            'Progress'      => '_standing',
            'Points'        => '_points',
            'Diff'          => '_pointDiff',
            'WF'            => '_worldFirst',
            'RF'            => '_regionFirst',
            'SF'            => '_serverFirst',
            'Trending'      => '_trend',
            'Previous Rank' => '_prevRank'
        );

    const TABLE_HEADER_STANDINGS_NEWS = array(
            'Rank'     => '_rank',
            'Guild'    => '_nameLink',
            'Server'   => '_serverLink',
            'Progress' => '_standing'
        );

    /**
     * constructor
     */
    public function __construct($listType, $params) {
        $this->_listType = $listType;

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $this->_guildArray[$guildId] = clone($guildDetails);
        }

        switch ($this->_listType) {
            case 'standings':
                if ( isset($params[0]) ) { $this->_view    = $params[0]; }
                if ( isset($params[1]) ) { $this->_tier    = $params[1]; }
                if ( isset($params[2]) ) { $this->_dungeon = $params[2]; }
                if ( isset($params[3]) ) {
                    if ( $params[3] == 'spreadsheet' ) {
                        $this->_isSpreadsheet = true;
                    } else {
                      $this->_encounter = $params[3];
                    }
                }
                break;
            case 'rankings':
                if ( isset($params[0]) ) { $this->_view     = $params[0]; }
                if ( isset($params[1]) ) { $this->_rankType = $params[1]; }
                if ( isset($params[2]) ) { $this->_tier     = $params[2]; }
                if ( isset($params[3]) ) { $this->_dungeon  = $params[3]; }
                break;
            case 'servers':
                if ( isset($params[0]) ) { $this->_server = $params[0]; }
                if ( isset($params[1]) ) { 
                    $this->_tier = $params[1];
                } else {
                    $this->_tier = Functions::cleanLink(CommonDataContainer::$tierArray[LATEST_TIER]->_name);
                }

                $this->_serverDetails = Functions::getServerByName($this->_server);
                $this->_serverDetails->getGuilds();
                break;
            case 'news':
                if ( isset($params[0]) ) { $this->_view    = $params[0]; }
                if ( isset($params[1]) ) { $this->_tier    = $params[1]; }
                if ( isset($params[2]) ) { $this->_dungeon = $params[2]; }

                break;
        }

        $this->_getDataDetails($this->_tier, $this->_dungeon, $this->_encounter);

        switch ($this->_listType) {
            case 'standings':
            case 'news':
                $this->listArray = $this->_getStandings();
                break;
            case 'rankings':
                $this->listArray = $this->_getRankings();
                break;
            case 'servers':
                $this->listArray = $this->_getServerStandings();
                break;
        }
    }

    /**
     * get model data details based on url parameters
     * 
     * @param  string $tier      [ name of tier ]
     * @param  string $dungeon   [ name of dungeon ]
     * @param  string $encounter [ name of encounter ]
     * 
     * @return mixed [ null if dataDetails are empty ]
     */
    private function _getDataDetails($tier, $dungeon, $encounter) {
        if ( !empty($this->_encounter) ) {
            $this->_dataType    = '_encounterDetails'; 
            $this->_dataDetails = Functions::getEncounterByName($this->_encounter, $this->_dungeon);
            $this->_tableHeader = self::TABLE_HEADER_STANDINGS_ENCOUNTER;
            $this->_identifier  = $this->_dataDetails->_encounterId;
            $this->_title       = $this->_dataDetails->_encounterName;

            if ( empty($this->_dataDetails) ) { return null; }


        } elseif ( !empty($this->_dungeon) ) {
            $this->_dataType    = '_dungeonDetails';
            $this->_dataDetails = Functions::getDungeonByName($this->_dungeon);
            $this->_tableHeader = self::TABLE_HEADER_STANDINGS_DUNGEON;
            $this->_identifier  = $this->_dataDetails->_dungeonId;
            $this->_title       = $this->_dataDetails->_name;

            if ( empty($this->_dataDetails) ) { return null; }

            if ( $this->_listType == 'rankings' ) {
                $systemDetails = Functions::getRankSystemByName($this->_rankType);
                $systemId      = $systemDetails->_abbreviation;

                $this->_rankingsType  = '_rankDungeons';
                $this->_standingsType = '_dungeonDetails';
                $this->_standingsId = $this->_dataDetails->_dungeonId;
                $this->_rankingId   = $this->_standingsId . '_' . $systemId;
                $this->title        = $this->_dataDetails->_name;
            }

            if ( $this->_listType == 'news' ) {
                $this->_tableHeader = self::TABLE_HEADER_STANDINGS_NEWS;
            }
        } elseif ( !empty($this->_tier) ) {
            $this->_dataType    = '_tierDetails';
            $this->_dataDetails = Functions::getTierByName($this->_tier);
            $this->_tierDetails = $this->_dataDetails;
            $this->_tableHeader = self::TABLE_HEADER_STANDINGS_DUNGEON;
            $this->_identifier  = $this->_dataDetails->_tier;
        }

        if ( empty($this->_dataDetails) ) { Functions::sendTo404(); }
    }

    /**
     * TEMPORARY
     *
     * @return array
     */
    private function _getTemporarySortArray($dungeonDetails = null) {
        $sortArray = array();

        if ( $this->_listType == 'standings' || $this->_listType == 'news' ) {
            foreach ( $this->_guildArray as $guildId => $guildDetails ) {
                switch ($this->_dataType) {
                    case '_tierDetails':
                        $guildDetails->generateEncounterDetails('tier', $this->_identifier);
                        break;
                    case '_dungeonDetails':
                        $guildDetails->generateEncounterDetails('dungeon', $this->_identifier);
                        break;
                    case '_encounterDetails':
                        $guildDetails->generateEncounterDetails('encounter', $this->_identifier);
                        break;
                }

                if ( empty($guildDetails->{$this->_dataType}->{$this->_identifier}) ) { continue; }

                $progressionDetails = $guildDetails->{$this->_dataType}->{$this->_identifier};

                if ( $this->_dataType != '_encounterDetails' && $progressionDetails->_complete == 0 ) { continue; }
 
                $this->_guildArray[$guildId]->mergeViewDetails($this->_dataType, $this->_identifier);

                if ( $this->_dataType == '_encounterDetails' ) { 
                    $sortArray[0][$guildId] = $progressionDetails->_strtotime; 
                } elseif ( $this->_dataType != '_encounterDetails' ) { 
                    $sortArray[$progressionDetails->_complete][$guildId] = $progressionDetails->_recentTime;

                    // If guild only submitted the final encounter, their complete will be equal to dungeon completion
                    if ( $progressionDetails->_complete != $this->_dataDetails->_numOfEncounters ) {
                        $finalEncounterId = $this->_dataDetails->_finalEncounterId;

                        if ( isset($guildDetails->_encounterDetails->$finalEncounterId) ) {
                            $totalComplete = CommonDataContainer::$dungeonArray[$this->_identifier]->_numOfEncounters;

                            $sortArray[$totalComplete][$guildId] = $progressionDetails->_recentTime;
                        }
                    }
                }
            }
        } elseif ( $this->_listType == 'rankings' ) {
            foreach ( $this->_guildArray as $guildId => $guildDetails ) {
                $guildDetails->generateEncounterDetails('dungeon', $this->_standingsId);
                $guildDetails->generateRankDetails('dungeons');

                if ( !isset($guildDetails->_rankDetails->{$this->_rankingsType}->{$this->_rankingId}) ) { continue; }

                $points = $guildDetails->_rankDetails->{$this->_rankingsType}->{$this->_rankingId}->_points;

                $sortArray[$guildId] = $points;

                $this->_tableHeader = self::TABLE_HEADER_RANKINGS_DEFAULT;
            }
        } elseif ( $this->_listType == 'servers' ) {
            $dungeonId = $dungeonDetails->_dungeonId;

            foreach ( $this->_serverDetails->_guilds as $guildId => $guildDetails ) {
                $this->_dungeonGuildArray[$guildId] = clone($guildDetails);

                $guildDetails->generateEncounterDetails('dungeon', $dungeonId);

                if ( empty($guildDetails->_dungeonDetails->$dungeonId->_complete) ) { continue; }

                $progressionDetails = $guildDetails->_dungeonDetails->$dungeonId;

                $this->_dungeonGuildArray[$guildId]->mergeViewDetails('_dungeonDetails', $dungeonId);

                $sortArray[$progressionDetails->_complete][$guildId] = $progressionDetails->_recentTime; 
            }
        }

        return $sortArray;
    }

    /**
     * TEMPORARY
     *
     * @return void
     */
    private function _addGuildToListArray(&$guildDetails, &$temporaryGuildArray, &$completionTimeArray, &$rankArray) {
        $guildId = $guildDetails->_guildId;

        $guildDetails->getTimeDiff($completionTimeArray, $guildDetails->_strtotime);
        $guildDetails->_rank = $rankArray;

        $temporaryGuildArray[$guildId] = $guildDetails;
        $completionTimeArray           = $guildDetails->_strtotime;
        $rankArray++;
    }

    /**
     * TEMPORARY
     *
     * @return array
     */
    private function _getStandings() {
        $returnArray         = array();
        $temporarySortArray  = array();
        $rankArray           = array();
        $completionTimeArray = array();
        $sortGuildArray      = array();

        $temporarySortArray = $this->_getTemporarySortArray();

        if ( !empty($temporarySortArray) ) {
            krsort($temporarySortArray);

            foreach ( $temporarySortArray as $score => $temporaryGuildArray ) {
                asort($temporaryGuildArray);
              
                foreach ( $temporaryGuildArray as $guildId => $complete ) {
                    $guildDetails = $this->_guildArray[$guildId];
                    $server       = $guildDetails->_server;
                    $region       = $guildDetails->_region;
                    $country      = $guildDetails->_country;

                    if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = $this->_guildArray[$guildId]; }

                    switch ( $this->_view ) {
                        case 'world':
                            if ( !isset($completionTimeArray['world']) ) { $completionTimeArray['world'] = 0; }
                            if ( !isset($sortGuildArray['world']) ) { $sortGuildArray['world'] = array(); }
                            if ( !isset($rankArray['world']) ) { $rankArray['world'] = 1; }

                            $this->_addGuildToListArray($guildDetails, $sortGuildArray['world'], $completionTimeArray['world'], $rankArray['world']);
                            break;
                        case 'region':
                            if ( !isset($completionTimeArray['region'][$region]) ) { $completionTimeArray['region'][$region] = 0; }
                            if ( !isset($sortGuildArray['region'][$region]) ) { $sortGuildArray['region'][$region] = array(); }
                            if ( !isset($rankArray['region'][$region]) ) { $rankArray['region'][$region] = 1; }

                            $this->_addGuildToListArray($guildDetails, $sortGuildArray['region'][$region], $completionTimeArray['region'][$region], $rankArray['region'][$region]);
                            break;
                        case 'server':
                            if ( !isset($completionTimeArray['server'][$server]) ) { $completionTimeArray['server'][$server] = 0; }
                            if ( !isset($sortGuildArray['server'][$server]) ) { $sortGuildArray['server'][$server] = array(); }
                            if ( !isset($rankArray['server'][$server]) ) { $rankArray['server'][$server] = 1; }

                            $this->_addGuildToListArray($guildDetails, $sortGuildArray['server'][$server], $completionTimeArray['server'][$server], $rankArray['server'][$server]);
                            break;
                        case 'country':
                            if ( !isset($completionTimeArray['country'][$country]) ) { $completionTimeArray['country'][$country] = 0; }
                            if ( !isset($sortGuildArray['country'][$country]) ) { $sortGuildArray['country'][$country] = array(); }
                            if ( !isset($rankArray['country'][$country]) ) { $rankArray['country'][$country] = 1; }

                            $this->_addGuildToListArray($guildDetails, $sortGuildArray['country'][$country], $completionTimeArray['country'][$country], $rankArray['country'][$country]);
                            break;
                    }
                }
            }
        }

        $returnArray = $this->_setViewArray($this->_view, $sortGuildArray);

        return $returnArray;
    }

    /**
     * get guild rankings based upon type of content selected
     * 
     * @param  string $rankType [ point ranking system ]
     * @param  string $view     [ view type filter ]
     * @param  string $tier     [ name of tier ]
     * @param  string $dungeon  [ name of dungeon ]
     * 
     * @return array [ array of guilds sorted by points ]
     */
    private function _getRankings() {
        $returnArray         = array();
        $temporarySortArray  = array();
        $completionTimeArray = array();
        $sortGuildArray      = array();
        $pointDiffArray      = array();

        $temporarySortArray = $this->_getTemporarySortArray();

        if ( !empty($temporarySortArray) ) {
            arsort($temporarySortArray);

            foreach ( $temporarySortArray as $guildId => $points ) {
                $this->_guildArray[$guildId]->mergeViewDetails($this->_standingsType, $this->_standingsId);
                $this->_guildArray[$guildId]->mergeRankViewDetails($this->_rankingsType, $this->_rankingId, $this->_view);

                if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = $this->_guildArray[$guildId]; }

                $guildDetails = $this->_guildArray[$guildId];
                $server       = $guildDetails->_server;
                $region       = $guildDetails->_region;
                $country      = $guildDetails->_country;

                switch ( $this->_view ) {
                    case 'world':
                        if ( !isset($pointDiffArray['world']) ) { $pointDiffArray['world'] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['world'], $guildDetails->_points);

                        $sortGuildArray['world'][$guildId] = $guildDetails;
                        $pointDiffArray['world']           = $guildDetails->_points;
                        break;
                    case 'region':
                        if ( !isset($pointDiffArray['region'][$region]) ) { $pointDiffArray['region'][$region] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['region'][$region], $guildDetails->_points);

                        $sortGuildArray['region'][$region][$guildId] = $guildDetails;
                        $pointDiffArray['region'][$region] = $guildDetails->_points;
                        break;
                    case 'server':
                        if ( !isset($pointDiffArray['server'][$server]) ) { $pointDiffArray['server'][$server] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['server'][$server], $guildDetails->_points);

                        $sortGuildArray['server'][$server][$guildId] = $guildDetails;
                        $pointDiffArray['server'][$server] = $guildDetails->_points;
                        break;
                    case 'country':
                        if ( !isset($pointDiffArray['country'][$country]) ) { $pointDiffArray['country'][$country] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['country'][$country], $guildDetails->_points);

                        $sortGuildArray['country'][$country][$guildId]  = $guildDetails;
                        $pointDiffArray['country'][$country] = $guildDetails->_points;
                        break;
                }

                $this->_guildArray[$guildId]->_points = Functions::formatPoints($points);
            }
        }

        $returnArray = $this->_setViewArray($this->_view, $sortGuildArray);

        return $returnArray;
    }

    /**
     * get guild standings based upon tier selected
     * 
     * @param  Tier $tierDetails [ tier data object ]
     * 
     * @return array [ array of guilds sorted by completion standings ]
     */
    private function _getServerStandings() {
        $returnArray = array();

        foreach ( $this->_dataDetails->_dungeons as $dungeonId => $dungeonDetails ) {
            $dungeonDetails      = CommonDataContainer::$dungeonArray[$dungeonId];
            $temporarySortArray  = array();
            $sortGuildArray      = array();
            $completionTimeArray = 0;
            $rankArray           = 1;

            $temporarySortArray = $this->_getTemporarySortArray($dungeonDetails);

            if ( !empty($temporarySortArray) ) {
                krsort($temporarySortArray);

                foreach ( $temporarySortArray as $score => $temporaryGuildArray ) {
                    asort($temporaryGuildArray);
                  
                    foreach ( $temporaryGuildArray as $guildId => $complete ) {
                        $guildDetails = $this->_dungeonGuildArray[$guildId];

                        if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = $guildDetails; }

                        if ( !isset($completionTimeArray) ) { $completionTimeArray = 0; }
                        if ( !isset($sortGuildArray) ) { $sortGuildArray = array(); }
                        if ( !isset($rankArray) ) { $rankArray = 1; }

                        $this->_addGuildToListArray($guildDetails, $sortGuildArray, $completionTimeArray, $rankArray);
                    }
                }
            }

            $returnArray[$dungeonId] = $this->_setViewServerArray($sortGuildArray, $dungeonDetails);
        }

        return $returnArray;
    }

    /**
     * TEMPORARY
     *
     * @return object
     */
    private function _setViewArray($viewType, $sortGuildArray) {
        $retVal = new stdClass();

        switch( $this->_view ) {
            case 'world':
                $retVal->world         = new stdClass();
                $retVal->world->header = ucfirst($this->_view) . ' ' . self::PAGE_NAME;
                $retVal->world->data   = (!empty($sortGuildArray['world']) ? $sortGuildArray['world'] : array());

                break;
            case 'region':
                foreach ( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $region       = $regionDetails->_name;
                    $abbreviation = $regionDetails->_abbreviation;

                    $retVal->$abbreviation         = new stdClass();
                    $retVal->$abbreviation->header = $regionDetails->_style . ' ' . self::PAGE_NAME;
                    $retVal->$abbreviation->data   = (!empty($sortGuildArray['region'][$abbreviation]) ? $sortGuildArray['region'][$abbreviation] : array());
                }

                break;
            case 'server':
                foreach ( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
                    $server = $serverDetails->_name;
                    $region = $serverDetails->_region;

                    $retVal->$server         = new stdClass();
                    $retVal->$server->header = $server . ' ' . self::PAGE_NAME;
                    $retVal->$server->data   = (!empty($sortGuildArray['server'][$server]) ? $sortGuildArray['server'][$server] : array());
                }

                break;
            case 'country':
                foreach ( CommonDataContainer::$countryArray as $countryId => $countryDetails ) {
                    $country = $countryDetails->_name;
                    $region  = $countryDetails->_region;

                    $retVal->$country         = new stdClass();
                    $retVal->$country->header = $country . ' ' . self::PAGE_NAME;
                    $retVal->$country->data   = (!empty($sortGuildArray['country'][$country]) ? $sortGuildArray['country'][$country] : array());
                }

                break;
        }

        return $retVal;
    }

    /**
     * set the standings header and guild array per view
     * 
     * @param array   $sortGuildArray [ array of guilds ]
     * @param Dungeon $dungeonDetails [ dungeon data object ]
     *
     * @return object [ standings object array ]
     */
    private function _setViewServerArray($sortGuildArray, $dungeonDetails) {
        $retVal         = new stdClass();
        $retVal->header = $dungeonDetails->_name . ' Standings';
        $retVal->data   = (!empty($sortGuildArray) ? $sortGuildArray : array());

        return $retVal;
    }
}