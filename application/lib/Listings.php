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
    protected $_title;
    protected $_isSpreadsheet;

    protected $_tierDetails;
    protected $_dungeonDetails;
    protected $_encounterDetails;

    // RANKINGS
    protected $_rankType;
    protected $_standingsType;
    protected $_standingsId;
    protected $_rankingId;

    // SERVERS
    protected $_serverDetails;

    public $listArray = array();

    /**
     * constructor
     */
    public function __construct($listType, $params, $serverDetails = null) {
        $this->_listType   = $listType;
        $this->_guildArray = CommonDataContainer::$guildArray;

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

                if ( isset($params[1]) ) { $this->_tier = $params[1];
                } else { $this->_tier = Functions::cleanLink(CommonDataContainer::$tierArray[LATEST_TIER]->_name); }

                if ( isset($params[2]) ) { $this->_dungeon = $params[2]; }

                if ( !empty($serverDetails) ) { $this->_serverDetails = $serverDetails;
                } else { $this->_serverDetails = Functions::getServerByName($this->_server); }

                if ( !isset($this->_serverDetails) ) {
                    Functions::sendTo404();
                }

                $this->_serverDetails->getGuilds();
                $this->_guildArray = $this->_serverDetails->_guilds;

                $this->_view = 'server';
                break;
            case 'news':
                if ( isset($params[0]) ) { $this->_view    = $params[0]; }
                if ( isset($params[1]) ) { $this->_tier    = $params[1]; }
                if ( isset($params[2]) ) { $this->_dungeon = $params[2]; }

                break;
        }

        if ( $this->_view != 'world'
          && $this->_view != 'region'
          && $this->_view != 'server'
          && $this->_view != 'country' ) {
            Functions::sendTo404();
        }

        $this->_getDataDetails($this->_tier, $this->_dungeon, $this->_encounter);

        switch ($this->_listType) {
            case 'standings':
            case 'news':
            case 'servers':
                $this->listArray = $this->_getStandings();
                break;
            case 'rankings':
                $this->listArray = $this->_getRankings();
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

             if ( !isset($this->_dataDetails->_encounterId) ) { Functions::sendTo404(); }

            $this->_identifier  = $this->_dataDetails->_encounterId;
            $this->_title       = $this->_dataDetails->_encounterName;

            DBFactory::getEncounterKills('encounter', $this->_identifier);

            if ( empty($this->_dataDetails) ) { return null; }

            $this->_encounterDetails = $this->_dataDetails;
            $this->_dungeonDetails   = CommonDataContainer::$dungeonArray[$this->_dataDetails->_dungeonId];
            $this->_tierDetails      = CommonDataContainer::$tierArray[$this->_dataDetails->_tier];
        } elseif ( !empty($this->_dungeon) ) {
            $this->_dataType    = '_dungeonDetails';
            $this->_dataDetails = Functions::getDungeonByName($this->_dungeon);

            if ( !isset($this->_dataDetails->_dungeonId) ) { Functions::sendTo404(); }

            $this->_identifier  = $this->_dataDetails->_dungeonId;
            $this->_title       = $this->_dataDetails->_name;

            if ( empty($this->_dataDetails) ) { return null; }

            $this->_dungeonDetails = $this->_dataDetails;
            $this->_tierDetails    = CommonDataContainer::$tierArray[$this->_dataDetails->_tier];

            if ( $this->_listType == 'rankings' ) {
                $systemDetails = Functions::getRankSystemByName($this->_rankType);

                if ( !isset($systemDetails) ) { Functions::sendTo404(); }

                $systemId      = $systemDetails->_abbreviation;

                $this->_standingsType = '_dungeonDetails';
                $this->_standingsId = $this->_dataDetails->_dungeonId;
                $this->_rankingId   = '_' . strtolower($systemId);
                $this->title        = $this->_dataDetails->_name;

                DBFactory::getEncounterKills('dungeon', $this->_standingsId);
            }
        } elseif ( !empty($this->_tier) ) {
            $this->_dataType    = '_tierDetails';
            $this->_dataDetails = Functions::getTierByName($this->_tier);

            if ( !isset($this->_dataDetails->_tier) ) { Functions::sendTo404(); }

            $this->_identifier  = $this->_dataDetails->_tier;

            if ( empty($this->_dataDetails) ) { return null; }

            $this->_tierDetails = $this->_dataDetails;
        }

        if ( empty($this->_dataDetails) ) { Functions::sendTo404(); }
    }

    /**
     * add guild to sorting array and increasing rank value
     * 
     * @param GuildDetails $guildDetails        [ guild details object ]
     * @param array        $temporaryGuildArray [ array of guilds in sorted by as they are being added]
     * @param array        $completionTimeArray [ completion time sorted by earliest to latest ]
     * @param array        $rankArray           [ rank increment in array format based on view ]
     *
     * @return void
     */
    private function _addGuildToListArray(&$guildDetails, &$temporaryGuildArray, &$completionTimeArray, &$rankArray) {
        $guildId = $guildDetails->_guildId;

        $guildDetails->_rank = $rankArray;

        $temporaryGuildArray[$guildId] = $guildDetails;
        $completionTimeArray           = $guildDetails->_strtotime;
        $rankArray++;
    }

    /**
     * get list of guilds based on standings information
     *
     * @return array [ array of guilds sorted by number of encounters cleared and time ]
     */
    private function _getStandings() {
        $rankArray           = array();
        $completionTimeArray = array();
        $sortGuildArray      = array();

        if ( $this->_encounter ) {
            $temporaryGuildArray = DbFactory::getStandingsForEncounter($this->_identifier, $this->_guildArray);
        } else {
            $temporaryGuildArray = DbFactory::getStandingsForDungeon($this->_identifier, $this->_guildArray);
        }

        if ( $this->_isSpreadsheet ) {
            DbFactory::getEncounterKills('dungeon', $this->_identifier);
        }

        foreach ( $temporaryGuildArray as $guildId => $complete ) {
            $guildDetails = $temporaryGuildArray[$guildId];
            $server       = $guildDetails->_server;
            $region       = $guildDetails->_region;
            $country      = $guildDetails->_country;

            if ( $this->_isSpreadsheet ) {
                $guildDetails = CommonDataContainer::$guildArray[$guildId];
                $guildDetails->generateEncounterDetails('dungeon', $this->_identifier);
            } elseif ( $this->_encounter ) {
                $guildDetails->generateEncounterDetails('encounter', $this->_identifier);

                $guildDetails = $guildDetails->_encounterDetails->{$this->_identifier};
            }

            if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = $guildDetails; }

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

        return $this->_setViewArray($sortGuildArray);
    }

    /**
     * get guild rankings based upon type of content selected
     * 
     * @return array [ array of guilds sorted by points ]
     */
    private function _getRankings() {
        $completionTimeArray = array();
        $sortGuildArray      = array();
        $pointDiffArray      = array();
        $newSortRankArray    = array();
        $temporaryGuildArray = DbFactory::getRankingsForDungeon($this->_identifier, $this->_rankType, $this->_view);

        foreach ( $temporaryGuildArray as $guildId => $guildDetails ) {
            $worldRank = $guildDetails->_worldRank;
            $newSortRankArray[$worldRank] = $guildDetails;
        }

        ksort($newSortRankArray);

        foreach ( $newSortRankArray as $listRank => $guildDetails ) {
            $guildId      = $guildDetails->_guildId;
            $server       = $guildDetails->_server;
            $region       = $guildDetails->_region;
            $country      = $guildDetails->_country;

            if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = $guildDetails; }

            switch ( $this->_view ) {
                case 'world':
                    if ( !isset($pointDiffArray['world']) ) { $pointDiffArray['world'] = 0; }

                    $sortGuildArray['world'][$guildId] = $guildDetails;
                    break;
                case 'region':
                    if ( !isset($pointDiffArray['region'][$region]) ) { $pointDiffArray['region'][$region] = 0; }

                    $sortGuildArray['region'][$region][$guildId] = $guildDetails;
                    break;
                case 'server':
                    if ( !isset($pointDiffArray['server'][$server]) ) { $pointDiffArray['server'][$server] = 0; }

                    $sortGuildArray['server'][$server][$guildId] = $guildDetails;
                    break;
                case 'country':
                    if ( !isset($pointDiffArray['country'][$country]) ) { $pointDiffArray['country'][$country] = 0; }

                    $sortGuildArray['country'][$country][$guildId]  = $guildDetails;
                    break;
            }
        }

        return $this->_setViewArray($sortGuildArray);
    }

    /**
     * take a sorted list of guilds and to sort them by the UI viewing property
     *
     * @param array $sortGuildArray [ list of guilds sorted by time of completion ]
     *
     * @return GuildListing [ guild listing object ]
     */
    private function _setViewArray($sortGuildArray) {
        $guildListing = new GuildListing();

        switch( $this->_view ) {
            case 'world':
                $guildListing->world                = array();
                $guildListing->world['world']       = new GuildListingDetails();
                $guildListing->world['world']->data = (!empty($sortGuildArray['world']) ? $sortGuildArray['world'] : array());

                break;
            case 'region':
                $guildListing->region = array();

                foreach ( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $guildListing->region[$regionId]       = new GuildListingDetails();
                    $guildListing->region[$regionId]->data = (!empty($sortGuildArray['region'][$regionId]) ? $sortGuildArray['region'][$regionId] : array());
                }

                break;
            case 'server':
                $guildListing->server = array();

                foreach ( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
                    $guildListing->server[$serverId]       = new GuildListingDetails();
                    $guildListing->server[$serverId]->data = (!empty($sortGuildArray['server'][$serverId]) ? $sortGuildArray['server'][$serverId] : array());
                }

                break;
            case 'country':
                $guildListing->country = array();

                foreach ( CommonDataContainer::$countryArray as $countryId => $countryDetails ) {
                    $guildListing->country[$countryId]        = new GuildListingDetails();
                    $guildListing->$country[$countryId]->data = (!empty($sortGuildArray['country'][$countryId]) ? $sortGuildArray['country'][$countryId] : array());
                }

                break;
        }

        return $guildListing;
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