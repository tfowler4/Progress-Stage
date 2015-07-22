<?php
class Listings {
    protected $_listType;
    protected $_view;
    protected $_raidSize;
    protected $_tier;
    protected $_tierSize;
    protected $_dungeon;
    protected $_encounter;
    protected $_identifier;
    protected $_dataType;
    public $listArray;

    const PAGE_NAME  = 'Standings';

    public function __construct($listType, $params) {
        $this->_listType   = $listType;
        $this->_identifier = $params[0];
        $this->_view       = $params[1];
        $this->_dataType   = '_dungeonDetails';

        switch ($this->_listType) {
            case 'standings':
                $this->listArray = $this->getStandings();
                break;
            case 'rankings':
                break;
        }
    }

    //TEMPORARY
    public function getTemporarySortArray() {
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
            }
        }

        return $sortArray;
    }

    //TEMPORARY
    public function addGuildToListArray(&$guildDetails, &$temporaryGuildArray, &$completionTimeArray, &$rankArray) {
        $guildId = $guildDetails->_guildId;

        $guildDetails->getTimeDiff($completionTimeArray, $guildDetails->_strtotime);
        $guildDetails->_rank = $rankArray;

        $temporaryGuildArray[$guildId] = $guildDetails;
        $completionTimeArray           = $guildDetails->_strtotime;
        $rankArray++;
    }

    //TEMPORARY
    public function getStandings() {
        $returnArray         = array();
        $temporarySortArray  = array();
        $rankArray           = array();
        $completionTimeArray = array();
        $sortGuildArray      = array();

        $temporarySortArray = $this->getTemporarySortArray();

        if ( !empty($temporarySortArray) ) {
            krsort($temporarySortArray);

            foreach ( $temporarySortArray as $score => $temporaryGuildArray ) {
                asort($temporaryGuildArray);
              
                foreach ( $temporaryGuildArray as $guildId => $complete ) {
                    $guildDetails = CommonDataContainer::$guildArray[$guildId];
                    $server       = $guildDetails->_server;
                    $region       = $guildDetails->_region;
                    $country      = $guildDetails->_country;

                    switch ( $this->_view ) {
                        case 'world':
                            if ( !isset($completionTimeArray['world']) ) { $completionTimeArray['world'] = 0; }
                            if ( !isset($sortGuildArray['world']) ) { $sortGuildArray['world'] = array(); }
                            if ( !isset($rankArray['world']) ) { $rankArray['world'] = 1; }

                            $this->addGuildToListArray($guildDetails, $sortGuildArray['world'], $completionTimeArray['world'], $rankArray['world']);
                            break;
                        case 'region':
                            if ( !isset($completionTimeArray['region'][$region]) ) { $completionTimeArray['region'][$region] = 0; }
                            if ( !isset($sortGuildArray['region'][$region]) ) { $sortGuildArray['region'][$region] = array(); }
                            if ( !isset($rankArray['region'][$region]) ) { $rankArray['region'][$region] = 1; }

                            $this->addGuildToListArray($guildDetails, $sortGuildArray['region'][$region], $completionTimeArray['region'][$region], $rankArray['region'][$region]);
                            break;
                        case 'server':
                            if ( !isset($completionTimeArray['server'][$server]) ) { $completionTimeArray['server'][$server] = 0; }
                            if ( !isset($sortGuildArray['server'][$server]) ) { $sortGuildArray['server'][$server] = array(); }
                            if ( !isset($rankArray['server'][$server]) ) { $rankArray['server'][$server] = 1; }

                            $this->addGuildToListArray($guildDetails, $sortGuildArray['server'][$server], $completionTimeArray['server'][$server], $rankArray['server'][$server]);
                            break;
                        case 'country':
                            if ( !isset($completionTimeArray['country'][$country]) ) { $completionTimeArray['country'][$country] = 0; }
                            if ( !isset($sortGuildArray['country'][$country]) ) { $sortGuildArray['country'][$country] = array(); }
                            if ( !isset($rankArray['country'][$country]) ) { $rankArray['country'][$country] = 1; }

                            $this->addGuildToListArray($guildDetails, $sortGuildArray['country'][$country], $completionTimeArray['country'][$country], $rankArray['country'][$country]);
                            break;
                    }
                }
            }
        }

        $returnArray = $this->setViewStandingsArray($this->_view, $sortGuildArray);

        return $returnArray;
    }

    //TEMPORARY
    public function setViewStandingsArray($viewType, $sortGuildArray) {
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
}