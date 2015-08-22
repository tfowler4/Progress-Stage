<?php

/**
 * progression standings guild listing page
 */
class StandingsModel extends Model {
    protected $_standingsArray = array();
    protected $_topGuildsArray = array();
    protected $_header;
    protected $_view;
    protected $_raidSize;
    protected $_tier;
    protected $_tierRaidSize;
    protected $_dungeon;
    protected $_encounter;
    protected $_detailsPane;
    protected $_dataDetails;
    protected $_dataType;
    protected $_identifier;
    protected $_isSpreadsheet;
    protected $_tableHeader;

    const TABLE_HEADER_DUNGEON = array(
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

    const TABLE_HEADER_ENCOUNTER = array(
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

    const GLOSSARY = array(
            'WF' => 'World Firsts',
            'RF' => 'Region Firsts',
            'SF' => 'Server Firsts',
            'WR' => 'World Rank',
            'RR' => 'Region Rank',
            'SR' => 'Server Rank'
        );

    const PANE_DUNGEON = array(
            'Name'              => '_name',
            'Tier'              => '_tierFullTitle',
            'Raid Size'         => '_raidSize',
            'Encounters'        => '_numOfEncounters',
            'Release Date'      => '_dateLaunch',
            'EU Time Diff'      => '_euTimeDiffTitle',
            'WW Clears'         => '_numOfDungeonClears',
            'NA Clears'         => '_numOfNADungeonClears',
            'EU Clears'         => '_numOfEUDungeonClears',
            'First Clear'       => '_firstDungeonClear',
            'Most Recent Clear' => '_recentDungeonClear'
        );

    const PANE_ENCOUNTER = array(
            'Name'             => '_name',
            'Tier'             => '_tierFullTitle',
            'Dungeon'          => '_dungeon',
            'Raid Size'        => '_raidSize',
            'WW Clears'        => '_numOfEncounterKills',
            'NA Clears'        => '_numOfNAEncounterKills',
            'EU Clears'        => '_numOfEUEncounterKills',
            'First Kill'       => '_firstEncounterKill',
            'Most Recent Kill' => '_recentEncounterKill'
        );

    const PAGE_TITLE = 'Progression Standings';
    const PAGE_NAME  = 'Standings';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

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
        
        $this->_getDataDetails($this->_tier, $this->_dungeon, $this->_encounter);

        $this->_detailsPane    = $this->_dataDetails;
        $this->_standingsArray = $this->_getStandings();
        $this->_dataDetails->setClears();
        
        $this->title .= ' ' . self::PAGE_NAME;
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
            $this->_tableHeader = self::TABLE_HEADER_ENCOUNTER;
            $this->_identifier  = $this->_dataDetails->_encounterId;
            $this->title        = $this->_dataDetails->_encounterName;

            if ( empty($this->_dataDetails) ) { return null; }
        } elseif ( !empty($this->_dungeon) ) {
            $this->_dataType    = '_dungeonDetails';
            $this->_dataDetails = Functions::getDungeonByName($this->_dungeon);
            $this->_tableHeader = self::TABLE_HEADER_DUNGEON;
            $this->_identifier  = $this->_dataDetails->_dungeonId;
            $this->title        = $this->_dataDetails->_name;

            if ( empty($this->_dataDetails) ) { return null; }
        }

        if ( empty($this->_dataDetails) ) { Functions::sendTo404(); }
    }

    /**
     * get sorted guild array based on number of encounters completed in dungeon
     *
     * @return array [ sorted guild array by amount completed ]
     */
    private function _getTemporarySortArray() {
        $sortArray = array();

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            switch ($this->_dataType) {
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

            CommonDataContainer::$guildArray[$guildId]->mergeViewDetails($this->_dataType, $this->_identifier);

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

        return $sortArray;
    }

    /**
     * add guild to the temporary guild array
     * 
     * @param GuildDetails &$guildDetails        [ guild details detail object ]
     * @param array        &$temporaryGuildArray [ list of guilds ]
     * @param array        &$completionTimeArray [ list of completion times ]
     * @param integer      &$rankCount           [ guild rank increment ]
     *
     * @return void
     */
    private function _addGuildToListArray(&$guildDetails, &$temporaryGuildArray, &$completionTimeArray, &$rankCount) {
        $guildId = $guildDetails->_guildId;

        $guildDetails->getTimeDiff($completionTimeArray, $guildDetails->_strtotime);
        $guildDetails->_rank = $rankCount;

        $temporaryGuildArray[$guildId] = $guildDetails;
        $completionTimeArray           = $guildDetails->_strtotime;
        $rankCount++;
    }

    /**
     * get guild standings based upon view and content selected
     * 
     * @return array [ array of guilds sorted by completion standings ]
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
                    $guildDetails = CommonDataContainer::$guildArray[$guildId];
                    $server       = $guildDetails->_server;
                    $region       = $guildDetails->_region;
                    $country      = $guildDetails->_country;

                    if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = CommonDataContainer::$guildArray[$guildId]; }

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

        $returnArray = $this->_setViewStandingsArray($this->_view, $sortGuildArray);

        return $returnArray;
    }

    /**
     * set the standings header and guild array per view
     *
     * @param string $viewType       [ view filter ex. server/region ]
     * @param array  $sortGuildArray [ array of guilds ]
     *
     * @return object [ standings object array ]
     */
    private function _setViewStandingsArray($viewType, $sortGuildArray) {
        $retVal = new stdClass();

        switch( $this->_view ) {
            case 'world':
                $retVal->world         = new stdClass();
                $retVal->world->header = ucfirst($this->_view) . ' ' . self::PAGE_NAME;
                $retVal->world->data   = (!empty($sortGuildArray['world']) ? $sortGuildArray['world'] : array());

                $this->title .= ' World';
                break;
            case 'region':
                foreach ( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $region       = $regionDetails->_name;
                    $abbreviation = $regionDetails->_abbreviation;

                    $retVal->$abbreviation         = new stdClass();
                    $retVal->$abbreviation->header = $regionDetails->_style . ' ' . self::PAGE_NAME;
                    $retVal->$abbreviation->data   = (!empty($sortGuildArray['region'][$abbreviation]) ? $sortGuildArray['region'][$abbreviation] : array());
                }

                $this->title .= ' Region';
                break;
            case 'server':
                foreach ( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
                    $server = $serverDetails->_name;
                    $region = $serverDetails->_region;

                    $retVal->$server         = new stdClass();
                    $retVal->$server->header = $server . ' ' . self::PAGE_NAME;
                    $retVal->$server->data   = (!empty($sortGuildArray['server'][$server]) ? $sortGuildArray['server'][$server] : array());
                }

                $this->title .= ' Server'; 
                break;
            case 'country':
                foreach ( CommonDataContainer::$countryArray as $countryId => $countryDetails ) {
                    $country = $countryDetails->_name;
                    $region  = $countryDetails->_region;

                    $retVal->$country         = new stdClass();
                    $retVal->$country->header = $country . ' ' . self::PAGE_NAME;
                    $retVal->$country->data   = (!empty($sortGuildArray['country'][$country]) ? $sortGuildArray['country'][$country] : array());
                }

                $this->title .= ' Country';
                break;
        }

        return $retVal;
    }

    /**
     * generate model specific internal links
     * 
     * @param  string  $view        [ view type filter ]
     * @param  string  $text        [ display text ]
     * @param  boolean $spreadsheet [ true if link to spreadsheet popup ]
     * 
     * @return string [ html hyperlink ]
     */
    public function generateInternalHyperLink($view, $text, $spreadsheet = false) {
        $url       = PAGE_STANDINGS . $view;
        $hyperlink = '';

        if ( isset($this->_tier) ) { $url .= '/' . $this->_tier; }
        if ( isset($this->_dungeon) ) { $url .= '/' . $this->_dungeon; }
        if ( isset($this->_encounter) ) { $url .= '/' . $this->_encounter; }
        if ( $spreadsheet ) { $url .= '/spreadsheet'; }

        $hyperlink = '<a href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}