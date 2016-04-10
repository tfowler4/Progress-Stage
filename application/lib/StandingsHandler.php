<?php

/**
 * standings object to handle the creation and updating process of guild dungeon/encounter standings
 */
class StandingsHandler {
    protected $_encounterDetails;
    protected $_dungeonDetails;
    protected $_currentDungeonStandings = array();
    protected $_currentEncounterStandings = array();
    protected $_newDungeonStandings = array();
    protected $_guildsWithKills = array();
    protected $_killTimeArray = array();

    const ENCOUNTER_MAPPER = array(
            'datetime'     => '_datetime',
            'date'         => '_date',
            'time'         => '_time',
            'time_zone'    => '_timezone',
            'server'       => '_server',
            'videos'       => '_numOfVideos',
            'server_rank'  => '_serverRank',
            'region_rank'  => '_regionRank',
            'world_rank'   => '_worldRank',
            'country_rank' => '_countryRank'
        );

    const DUNGEON_MAPPER = array(
            'complete'         => 'complete',
            'progress'         => 'progress',
            'special_progress' => 'special_progress',
            'achievement'      => 'achievement',
            'world_first'      => 'world_first',
            'region_first'     => 'region_first',
            'server_first'     => 'server_first',
            'country_first'    => 'country_first',
            'recent_activity'  => 'recent_activity',
            'recent_time'      => 'recent_time',
            'world_rank'       => 'world_rank',
            'region_rank'      => 'region_rank',
            'server_rank'      => 'server_rank',
            'country_rank'     => 'country_rank',
            'world_trend'      => 'world_trend',
            'region_trend'     => 'region_trend',
            'server_trend'     => 'server_trend',
            'country_trend'    => 'country_trend'
        );

    public function update($encounterId, $dungeonId) {
        $this->_encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
        $this->_dungeonDetails   = CommonDataContainer::$dungeonArray[$dungeonId];

        $this->_updateEncounterStandings();

        $this->_updateDungeonStandings();
    }

    protected function _updateDungeonStandings() {
        $dbh = DbFactory::getDbh();

        $this->_getExistingDungeonStandings();
        $this->_getAllDungeonEncounterKills();

        $this->_createStandings();

        $insertSQL      = sprintf("INSERT INTO %s
                                 (guild_id,
                                  dungeon_id,
                                  complete,
                                  progress,
                                  special_progress,
                                  achievement,
                                  world_first,
                                  region_first,
                                  server_first,
                                  country_first,
                                  recent_activity,
                                  recent_time,
                                  world_rank,
                                  region_rank,
                                  server_rank,
                                  country_rank,
                                  world_trend,
                                  region_trend,
                                  server_trend,
                                  country_trend)
                                  values", DbFactory::TABLE_STANDINGS);
        $insertValueSQL = '';
        $updateSQL      = '';

        // handle insert
        foreach ( $this->_newDungeonStandings as $guildId => $dungeonDetails ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];

            if ( !empty($insertValueSQL) ) {
                $insertValueSQL .= ',';
            }

            $insertValueSQL    .= '(';
            $dungeonValueSQL =  $guildId . ', ' . $this->_dungeonDetails->_dungeonId;

            foreach ( self::DUNGEON_MAPPER as $columnName => $value ) {
                if ( !empty($dungeonValueSQL) ) {
                    $dungeonValueSQL .= ',';
                }

                $dungeonValueSQL .= '"' . $dungeonDetails[$value] . '"';
            }

            $insertValueSQL .= $dungeonValueSQL . ')';
        }

        // handle update
        foreach ( self::DUNGEON_MAPPER as $columnName => $value ) {
            if ( !empty($updateSQL) ) {
                $updateSQL .= ',';
            }

            $updateSQL .= ' ' . $columnName . ' = CASE';

            foreach ( $this->_newDungeonStandings as $guildId => $dungeonDetails ) {
                $guildDetails = CommonDataContainer::$guildArray[$guildId];

                $updateSQL .= ' WHEN guild_id = "' . $guildId . '" AND dungeon_id = "' . $this->_dungeonDetails->_dungeonId . '" THEN "' .  $dungeonDetails[$value] . '"';
            }

            $updateSQL .= ' END';
        }

        if ( !empty($this->_newDungeonStandings) ) {
            $insertSQL = $insertSQL . ' ' . $insertValueSQL;
            $insertSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateSQL;
            $query = $dbh->prepare($insertSQL);
            $query->execute();
        }
    }

    protected function _getAllDungeonEncounterKills() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->query(sprintf(
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
              WHERE dungeon_id ='%d'",
                    DbFactory::TABLE_KILLS,
                    $this->_dungeonDetails->_dungeonId
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId          = $row['guild_id'];
            $encounterId      = $row['encounter_id'];
            $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonId        = $encounterDetails->_dungeonId;

            if ( isset(CommonDataContainer::$guildArray[$guildId]) ) {
                $guildDetails                             = CommonDataContainer::$guildArray[$guildId];
                $arr                                      = $guildDetails->_progression;
                $arr['dungeon'][$dungeonId][$encounterId] = $row;
                $arr['encounter'][$encounterId]           = $row;
                $guildDetails->_progression               = $arr;

                if ( !isset($this->_guildsWithKills[$guildId]) ) {
                    $this->_guildsWithKills[$guildId] = $guildDetails;
                }
            }
        }
    }

    protected function _createStandings() {
        foreach ( $this->_guildsWithKills as $guildId => $guildDetails ) {
            if ( !isset($guildDetails->_progression['dungeon']) ) { continue; }

            $this->_newDungeonStandings[$guildId] = array();

            foreach ( $guildDetails->_progression['dungeon'] as $dungeonId => $dungeonEncounterArray ) {
                if ( $dungeonId != $this->_dungeonDetails->_dungeonId ) { continue; }

                $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
                $detailsArray   = array();

                $detailsArray['guild_id']         = $guildId;
                $detailsArray['dungeon_id']       = $dungeonId;
                $detailsArray['complete']         = 0;
                $detailsArray['progress']         = 0;
                $detailsArray['special_progress'] = 0;
                $detailsArray['achievement']      = 'No';
                $detailsArray['world_first']      = 0;
                $detailsArray['region_first']     = 0;
                $detailsArray['server_first']     = 0;
                $detailsArray['country_first']    = 0;
                $detailsArray['recent_time']      = 0;
                $detailsArray['recent_activity']  = '';
                $detailsArray['world_rank']       = '';
                $detailsArray['region_rank']      = '';
                $detailsArray['server_rank']      = '';
                $detailsArray['country_rank']     = '';

                foreach ( $dungeonEncounterArray as $encounterId => $encounterDetails ) {
                    $encounter        = CommonDataContainer::$encounterArray[$encounterId];
                    $encounterDetails = new EncounterDetails($encounterDetails, $guildDetails, $dungeonDetails);

                    if ( $encounter->_type == 0 && $encounterDetails->_worldRank == 1 ) { $detailsArray['world_first']++; }
                    if ( $encounter->_type == 0 && $encounterDetails->_regionRank == 1 ) { $detailsArray['region_first']++; }
                    if ( $encounter->_type == 0 && $encounterDetails->_serverRank == 1 ) { $detailsArray['server_first']++; }
                    if ( $encounter->_type == 0 && $encounterDetails->_countryRank == 1 ) { $detailsArray['country_first']++; }

                    if ( $encounter->_type == 0 ) {
                        $detailsArray['progress']++;
                        $detailsArray['complete']++;
                    } elseif ( $encounter->_type == 1 ) {
                        $detailsArray['achievement'] = 'Yes';
                    } elseif ( $encounter->_type == 2 ) {
                        $detailsArray['special_progress']++;
                    }

                    if ( $encounter->_type == 0 && ($detailsArray['recent_time'] == 0 || $encounterDetails->_strtotime > $detailsArray['recent_time']) ) {
                        $detailsArray['recent_time']     = $encounterDetails->_strtotime;
                        $detailsArray['recent_activity'] = $encounter->_encounterName . ' @ ' . $encounterDetails->_datetime;
                    }
                }

                $detailsArray['progress']         .= '/' . $dungeonDetails->_numOfEncounters . ' ' . $dungeonDetails->_abbreviation;
                $detailsArray['special_progress'] .= '/' . $dungeonDetails->_numOfSpecialEncounters;

                // if any error occurs, or if complete is 0, scrap and dont add
                if ( $detailsArray['complete'] == 0 ) {
                    $detailsArray = array();
                }
            }

            if ( !empty($detailsArray) ) {
                $this->_newDungeonStandings[$guildId] = $detailsArray;
            }
        }

        // adding new ranking and trending
        $sortArray = array();

        foreach( $this->_newDungeonStandings as $guildId => $standingsDetails ) {
            if ( empty($standingsDetails) ) {
                unset($this->_newDungeonStandings[$guildId]);
                continue;
            }

            $complete = $standingsDetails['complete'];
            $time     = $standingsDetails['recent_time'];

            if ( !isset($sortArray[$complete]) ) {
                $sortArray[$complete] = array();
            }

            $sortArray[$complete][$guildId] = $time;
        }

        krsort($sortArray);

        foreach ( $sortArray as $complete => $guildArray ) {
            asort($guildArray);

            $sortArray[$complete] = $guildArray;
        }

        $overallStandingsArray = array();

        foreach ( $sortArray as $complete => $guildArray ) {
            foreach ( $guildArray as $guildId => $time ) {
                array_push($overallStandingsArray, $guildId);
            }
        }

        $rankArray  = array();
        foreach ( $overallStandingsArray as $guildId ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $server       = $guildDetails->_server;
            $region       = $guildDetails->_region;
            $country      = $guildDetails->_country;

            if ( !isset($rankArray['world']) ) {             $rankArray['world'] = 0; }
            if ( !isset($rankArray['server'][$server]) ) {   $rankArray['server'][$server] = 0; }
            if ( !isset($rankArray['region'][$region]) ) {   $rankArray['region'][$region] = 0; }
            if ( !isset($rankArray['country'][$country]) ) { $rankArray['country'][$country] = 0; }

            $rankArray['world']++;
            $rankArray['region'][$region]++;
            $rankArray['server'][$server]++;
            $rankArray['country'][$country]++;

            // check for trending
            if ( isset($this->_currentDungeonStandings[$guildId]) ) {
                $newStandingsDetails     = $this->_newDungeonStandings[$guildId];
                $currentStandingsDetails = $this->_currentDungeonStandings[$guildId];

                // current ranks and trends
                $currentWorldRank   = $currentStandingsDetails->_worldRank;
                $currentRegionRank  = $currentStandingsDetails->_regionRank;
                $currentServerRank  = $currentStandingsDetails->_serverRank;
                $currentCountryRank = $currentStandingsDetails->_countryRank;

                $currentWorldTrend   = $currentStandingsDetails->_worldTrend;
                $currentRegionTrend  = $currentStandingsDetails->_regionTrend;
                $currentServerTrend  = $currentStandingsDetails->_serverTrend;
                $currentCountryTrend = $currentStandingsDetails->_countryTrend;

                // new ranks
                $newStandingsDetails['world_rank']   =  $rankArray['world'];
                $newStandingsDetails['region_rank']  =  $rankArray['region'][$region];
                $newStandingsDetails['server_rank']  =  $rankArray['server'][$server];
                $newStandingsDetails['country_rank'] =  $rankArray['country'][$country];

                // setting trend
                if ( empty($currentWorldRank) )    { $newStandingsDetails['world_trend'] = 'NEW'; }
                if ( empty($currentRegionRank) )   { $newStandingsDetails['region_trend'] = 'NEW'; }
                if ( empty($currentServerRank) )   { $newStandingsDetails['server_trend'] = 'NEW'; }
                if ( empty($ccurrentCountryRank) ) { $newStandingsDetails['country_trend'] = 'NEW'; }

                if ( !empty($currentWorldRank) ) {
                    if ( $currentWorldRank > $rankArray['world'] ) {  $newStandingsDetails['world_trend'] = $currentWorldRank - $rankArray['world']; }
                    if ( $currentWorldRank < $rankArray['world'] ) {  $newStandingsDetails['world_trend'] = -1 * ($rankArray['world'] - $currentWorldRank); }
                    if ( $currentWorldRank == $rankArray['world'] ) { $newStandingsDetails['world_trend'] = '--'; }
                }

                if ( !empty($currentRegionRank) ) {
                    if ( $currentRegionRank > $rankArray['region'][$region] ) {  $newStandingsDetails['region_trend'] = $currentRegionRank - $rankArray['region'][$region]; }
                    if ( $currentRegionRank < $rankArray['region'][$region] ) {  $newStandingsDetails['region_trend'] = -1 * ($rankArray['region'][$region] - $currentRegionRank); }
                    if ( $currentRegionRank == $rankArray['region'][$region] ) { $newStandingsDetails['region_trend'] = '--'; }
                }

                if ( !empty($currentServerRank) ) {
                    if ( $currentServerRank > $rankArray['server'][$server] ) {  $newStandingsDetails['server_trend'] = $currentServerRank - $rankArray['server'][$server]; }
                    if ( $currentServerRank < $rankArray['server'][$server] ) {  $newStandingsDetails['server_trend'] = -1 * ($rankArray['server'][$server] - $currentServerRank); }
                    if ( $currentServerRank == $rankArray['server'][$server] ) { $newStandingsDetails['server_trend'] = '--'; }
                }

                if ( !empty($currentCountryRank) ) {
                    if ( $currentCountryRank > $rankArray['country'][$country] ) {  $newStandingsDetails['country_trend'] = $currentCountryRank - $rankArray['country'][$country]; }
                    if ( $currentCountryRank < $rankArray['country'][$country] ) {  $newStandingsDetails['country_trend'] = -1 * ($rankArray['country'][$country] - $currentCountryRank); }
                    if ( $currentCountryRank == $rankArray['country'][$country] ) { $newStandingsDetails['country_trend'] = '--'; }
                }

                $this->_newDungeonStandings[$guildId] = $newStandingsDetails;
            } else {
                $newStandingsDetails                  = $this->_newDungeonStandings[$guildId];
                $newStandingsDetails['world_trend']   = 'NEW';
                $newStandingsDetails['region_trend']  = 'NEW';
                $newStandingsDetails['server_trend']  = 'NEW';
                $newStandingsDetails['country_trend'] = 'NEW';
                $this->_newDungeonStandings[$guildId] = $newStandingsDetails;
            }
        }
    }

    protected function _getExistingDungeonStandings() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->query(sprintf(
            "SELECT standings_id,
                    guild_id,
                    dungeon_id
               FROM %s
              WHERE dungeon_id = '%d'",
                    DbFactory::TABLE_STANDINGS,
                    $this->_dungeonDetails->_dungeonId
        ));

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId = $row['guild_id'];

            if ( !isset(CommonDataContainer::$guildArray[$guildId]) ) { continue; }

            $this->_currentDungeonStandings[$guildId] = $row;
        }

        $this->_currentDungeonStandings = DbFactory::getStandingsForDungeon($this->_dungeonDetails->_dungeonId, $this->_currentDungeonStandings);
    }

    protected function _updateEncounterStandings() {
        $dbh = DbFactory::getDbh();

        $this->_currentEncounterStandings = DbFactory::getStandingsForEncounter($this->_encounterDetails->_encounterId);

        $this->_sortKillsByTime();
        $this->_setNewKillRanks();

        $insertSQL      = sprintf("INSERT INTO %s
                                 (guild_id,
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
                                  country_rank)
                                  values", DbFactory::TABLE_KILLS);
        $insertValueSQL = '';
        $updateSQL      = '';

        // handle insert
        foreach ( $this->_killTimeArray as $guildId => $killTime ) {
            $guildDetails     = CommonDataContainer::$guildArray[$guildId];
            $encounterDetails = $guildDetails->_encounterDetails->{$this->_encounterDetails->_encounterId};

            if ( !empty($insertValueSQL) ) {
                $insertValueSQL .= ',';
            }

            $insertValueSQL    .= '(';
            $encounterValueSQL =  $guildId . ', ' . $encounterDetails->_encounterId . ', ' . $encounterDetails->_dungeonId . ', ' . $encounterDetails->_tier . ', ' . $encounterDetails->_raidSize;

            foreach ( self::ENCOUNTER_MAPPER as $columnName => $value ) {
                if ( !empty($encounterValueSQL) ) {
                    $encounterValueSQL .= ',';
                }

                if ( $columnName == 'datetime' ) {
                    $encounterDetails->$value = $encounterDetails->_date . ' ' . $encounterDetails->_time;
                }

                $encounterValueSQL .= "'" . $encounterDetails->$value . "'";
            }

            $insertValueSQL .= $encounterValueSQL . ')';
        }

        // handle update
        foreach ( self::ENCOUNTER_MAPPER as $columnName => $value ) {
            if ( !empty($updateSQL) ) {
                $updateSQL .= ',';
            }

            $updateSQL .= ' ' . $columnName . ' = CASE';

            foreach ( $this->_killTimeArray as $guildId => $killTime ) {
                $guildDetails     = CommonDataContainer::$guildArray[$guildId];
                $encounterDetails = $guildDetails->_encounterDetails->{$this->_encounterDetails->_encounterId};

                if ( $columnName == 'datetime' ) {
                    $encounterDetails->$value = $encounterDetails->_date . ' ' . $encounterDetails->_time;
                }

                $updateSQL .= " WHEN guild_id = '" . $guildId . "' AND encounter_id = '" . $encounterDetails->_encounterId . "' THEN '" . $encounterDetails->$value . "'";
            }

            $updateSQL .= ' END';
        }

        if ( !empty($this->_killTimeArray) ) {
            $insertSQL = $insertSQL . ' ' . $insertValueSQL;
            $insertSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateSQL;
            $query = $dbh->prepare($insertSQL);
            
            $query->execute();
        }
    }

    protected function _sortKillsByTime() {
        foreach( $this->_currentEncounterStandings as $guildId => $guildDetails) {
            $guildDetails->generateEncounterDetails('encounter', $this->_encounterDetails->_encounterId);

            $this->_killTimeArray[$guildId] = $guildDetails->_encounterDetails->{$this->_encounterDetails->_encounterId}->_strtotime;
        }

        asort($this->_killTimeArray);
    }

    protected function _setNewKillRanks() {
        // New Rank Array per Encounter
        $rankArray   = array();
        $encounterId = $this->_encounterDetails->_encounterId;

        foreach ( $this->_killTimeArray as $guildId => $killTime ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $server       = $guildDetails->_server;
            $region       = $guildDetails->_region;
            $country      = $guildDetails->_country;

            if ( !isset($rankArray['world']) ) { $rankArray['world'] = 1; }
            if ( !isset($rankArray['server'][$server]) ) { $rankArray['server'][$server] = 1; }
            if ( !isset($rankArray['region'][$region]) ) { $rankArray['region'][$region] = 1; }
            if ( !isset($rankArray['country'][$country]) ) { $rankArray['country'][$country] = 1; }

            $guildDetails->_encounterDetails->$encounterId->_worldRank   = $rankArray['world'];
            $guildDetails->_encounterDetails->$encounterId->_serverRank  = $rankArray['server'][$server];
            $guildDetails->_encounterDetails->$encounterId->_regionRank  = $rankArray['region'][$region];
            $guildDetails->_encounterDetails->$encounterId->_countryRank = $rankArray['country'][$country];

            $rankArray['world']++;
            $rankArray['server'][$server]++;
            $rankArray['region'][$region]++;
            $rankArray['country'][$country]++;
        }
    }
}