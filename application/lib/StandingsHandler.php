<?php

/**
 * standings object to handle the creation and updating process of guild dungeon/encounter standings
 */
class StandingsHandler {
    protected static $_encounterDetails;
    protected static $_dungeonDetails;
    protected static $_currentDungeonStandings = array();
    protected static $_currentEncounterStandings = array();
    protected static $_newDungeonStandings = array();
    protected static $_guildsWithKills = array();
    protected static $_killTimeArray = array();

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
            'recent_time'      => 'recent_time'
        );

    public static function update($guildId, $encounterId, $dungeonId) {
        self::$_encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
        self::$_dungeonDetails   = CommonDataContainer::$dungeonArray[$dungeonId];

        self::_updateEncounterStandings();

        self::_updateDungeonStandings();
    }

    protected static function _updateDungeonStandings() {
        $dbh = DbFactory::getDbh();

        self::_getExistingDungeonStandings();
        self::_getAllDungeonEncounterKills();

        self::_createStandings();

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
                                  recent_time)
                                  values", DbFactory::TABLE_STANDINGS);
        $insertValueSQL = '';
        $updateSQL      = '';

        // handle insert
        foreach ( self::$_newDungeonStandings as $guildId => $dungeonDetails ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];

            if ( !empty($insertValueSQL) ) {
                $insertValueSQL .= ',';
            }

            $insertValueSQL    .= '(';
            $dungeonValueSQL =  $guildId . ', ' . self::$_dungeonDetails->_dungeonId;

            foreach ( self::DUNGEON_MAPPER as $columnName => $value ) {
                if ( !empty($dungeonValueSQL) ) {
                    $dungeonValueSQL .= ',';
                }

                $dungeonValueSQL .= "'" . $dungeonDetails[$value] . "'";
            }

            $insertValueSQL .= $dungeonValueSQL . ')';
        }

        // handle update
        foreach ( self::DUNGEON_MAPPER as $columnName => $value ) {
            if ( !empty($updateSQL) ) {
                $updateSQL .= ',';
            }

            $updateSQL .= ' ' . $columnName . ' = CASE';

            foreach ( self::$_newDungeonStandings as $guildId => $dungeonDetails ) {
                $guildDetails = CommonDataContainer::$guildArray[$guildId];

                $updateSQL .= " WHEN guild_id = '" . $guildId . "' AND dungeon_id = '" . self::$_dungeonDetails->_dungeonId . "' THEN '" . $dungeonDetails[$value] . "'";
            }

            $updateSQL .= ' END';
        }

        $insertSQL = $insertSQL . ' ' . $insertValueSQL;
        $insertSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateSQL;
        $query = $dbh->prepare($insertSQL);
        $query->execute();
    }

    protected static function _getAllDungeonEncounterKills() {
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
                    self::$_dungeonDetails->_dungeonId
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

                if ( !isset(self::$_guildsWithKills[$guildId]) ) {
                    self::$_guildsWithKills[$guildId] = $guildDetails;
                }
            }
        }
    }

    protected static function _createStandings() {
        foreach ( self::$_guildsWithKills as $guildId => $guildDetails ) {
            if ( !isset($guildDetails->_progression['dungeon']) ) { continue; }

            self::$_newDungeonStandings[$guildId] = array();

            foreach ( $guildDetails->_progression['dungeon'] as $dungeonId => $dungeonEncounterArray ) {
                if ( $dungeonId != self::$_dungeonDetails->_dungeonId ) { continue; }

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
                self::$_newDungeonStandings[$guildId] = $detailsArray;
            }
        }
    }

    protected static function _getExistingDungeonStandings() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->query(sprintf(
            "SELECT standings_id,
                    guild_id,
                    dungeon_id
               FROM %s
              WHERE dungeon_id = '%d'",
                    DbFactory::TABLE_STANDINGS,
                    self::$_dungeonDetails->_dungeonId
        ));

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId = $row['guild_id'];

            if ( !isset(CommonDataContainer::$guildArray[$guildId]) ) { continue; }

            self::$_currentDungeonStandings[$guildId] = $row;
        }
    }

    protected static function _updateEncounterStandings() {
        $dbh = DbFactory::getDbh();

        self::$_currentEncounterStandings = DbFactory::getStandingsForEncounter(self::$_encounterDetails->_encounterId);

        self::_sortKillsByTime();
        self::_setNewKillRanks();

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
        foreach ( self::$_killTimeArray as $guildId => $killTime ) {
            $guildDetails     = CommonDataContainer::$guildArray[$guildId];
            $encounterDetails = $guildDetails->_encounterDetails->{self::$_encounterDetails->_encounterId};

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

            foreach ( self::$_killTimeArray as $guildId => $killTime ) {
                $guildDetails     = CommonDataContainer::$guildArray[$guildId];
                $encounterDetails = $guildDetails->_encounterDetails->{self::$_encounterDetails->_encounterId};

                if ( $columnName == 'datetime' ) {
                    $encounterDetails->$value = $encounterDetails->_date . ' ' . $encounterDetails->_time;
                }

                $updateSQL .= " WHEN guild_id = '" . $guildId . "' AND encounter_id = '" . $encounterDetails->_encounterId . "' THEN '" . $encounterDetails->$value . "'";
            }

            $updateSQL .= ' END';
        }

        $insertSQL = $insertSQL . ' ' . $insertValueSQL;
        $insertSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateSQL;
        $query = $dbh->prepare($insertSQL);
        $query->execute();
    }

    protected static function _sortKillsByTime() {
        foreach( self::$_currentEncounterStandings as $guildId => $guildDetails) {
            $guildDetails->generateEncounterDetails('encounter', self::$_encounterDetails->_encounterId);

            self::$_killTimeArray[$guildId] = $guildDetails->_encounterDetails->{self::$_encounterDetails->_encounterId}->_strtotime;
        }

        asort(self::$_killTimeArray);
    }

    protected static function _setNewKillRanks() {
        // New Rank Array per Encounter
        $rankArray   = array();
        $encounterId = self::$_encounterDetails->_encounterId;

        foreach ( self::$_killTimeArray as $guildId => $killTime ) {
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