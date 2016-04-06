<?php

/**
 * rankings object to handle the creation and updating process of guild dungeon/encounter rankings
 */
class RankingsHandler {
    protected static $_currentQPDungeonRankings  = array();
    protected static $_currentAPDungeonRankings  = array();
    protected static $_currentAPFDungeonRankings = array();
    protected static $_currentEncounterStandings = array();
    protected static $_encounterKillTimeArray    = array();
    protected static $_numOfGuildsActiveInDungeonArray = array();
    protected static $_highestNumOfKillsInDungeonArray = array();
    protected static $_dungeonPointArray = array();
    protected static $_encounterPointArray = array();
    protected static $_rankArrayForEncounters = array();
    protected static $_rankArrayForDungeons = array();
    protected static $_existingEncounterRankingsArray = array();
    protected static $_dungeonDetails;
    protected static $_encounterDetails;

    /*
    const RANKING_MAPPER = array(
        'progress'              => 'progress',
        'special_progress'      => 'special_progress',
        '_points'             => '_points',
        '_world_rank'         => '_world_rank',
        '_region_rank'        => '_region_rank',
        '_server_rank'        => '_server_rank',
        '_country_rank'       => '_country_rank',
        '_world_trend'        => '_world_trend',
        '_region_trend'       => '_region_trend',
        '_server_trend'       => '_server_trend',
        '_country_trend'      => '_country_trend',
        '_world_prev_rank'    => '_world_prev_rank',
        '_region_prev_rank'   => '_region_prev_rank',
        '_server_prev_rank'   => '_server_prev_rank',
        '_country_prev_rank'  => '_country_prev_rank'
        );
    */
    const RANKING_QP_MAPPER = array(
        'progress'              => 'progress',
        'special_progress'      => 'special_progress',
        'qp_points'             => 'qp_points',
        'qp_world_rank'         => 'qp_world_rank',
        'qp_region_rank'        => 'qp_region_rank',
        'qp_server_rank'        => 'qp_server_rank',
        'qp_country_rank'       => 'qp_country_rank',
        'qp_world_trend'        => 'qp_world_trend',
        'qp_region_trend'       => 'qp_region_trend',
        'qp_server_trend'       => 'qp_server_trend',
        'qp_country_trend'      => 'qp_country_trend',
        'qp_world_prev_rank'    => 'qp_world_prev_rank',
        'qp_region_prev_rank'   => 'qp_region_prev_rank',
        'qp_server_prev_rank'   => 'qp_server_prev_rank',
        'qp_country_prev_rank'  => 'qp_country_prev_rank'
        );

    const RANKING_AP_MAPPER = array(
        'progress'              => 'progress',
        'special_progress'      => 'special_progress',
        'ap_points'             => 'ap_points',
        'ap_world_rank'         => 'ap_world_rank',
        'ap_region_rank'        => 'ap_region_rank',
        'ap_server_rank'        => 'ap_server_rank',
        'ap_country_rank'       => 'ap_country_rank',
        'ap_world_trend'        => 'ap_world_trend',
        'ap_region_trend'       => 'ap_region_trend',
        'ap_server_trend'       => 'ap_server_trend',
        'ap_country_trend'      => 'ap_country_trend',
        'ap_world_prev_rank'    => 'ap_world_prev_rank',
        'ap_region_prev_rank'   => 'ap_region_prev_rank',
        'ap_server_prev_rank'   => 'ap_server_prev_rank',
        'ap_country_prev_rank'  => 'ap_country_prev_rank'
        );

    const RANKING_APF_MAPPER = array(
        'progress'              => 'progress',
        'special_progress'      => 'special_progress',
        'apf_points'            => 'apf_points',
        'apf_world_rank'        => 'apf_world_rank',
        'apf_region_rank'       => 'apf_region_rank',
        'apf_server_rank'       => 'apf_server_rank',
        'apf_country_rank'      => 'apf_country_rank',
        'apf_world_trend'       => 'apf_world_trend',
        'apf_region_trend'      => 'apf_region_trend',
        'apf_server_trend'      => 'apf_server_trend',
        'apf_country_trend'     => 'apf_country_trend',
        'apf_world_prev_rank'   => 'apf_world_prev_rank',
        'apf_region_prev_rank'  => 'apf_region_prev_rank',
        'apf_server_prev_rank'  => 'apf_server_prev_rank',
        'apf_country_prev_rank' => 'apf_country_prev_rank'
        );

    const RANKING_MAPPER = array(
        'progress'              => 'progress',
        'special_progress'      => 'special_progress',
        'qp_points'             => 'qp_points',
        'qp_world_rank'         => 'qp_world_rank',
        'qp_region_rank'        => 'qp_region_rank',
        'qp_server_rank'        => 'qp_server_rank',
        'qp_country_rank'       => 'qp_country_rank',
        'qp_world_trend'        => 'qp_world_trend',
        'qp_region_trend'       => 'qp_region_trend',
        'qp_server_trend'       => 'qp_server_trend',
        'qp_country_trend'      => 'qp_country_trend',
        'qp_world_prev_rank'    => 'qp_world_prev_rank',
        'qp_region_prev_rank'   => 'qp_region_prev_rank',
        'qp_server_prev_rank'   => 'qp_server_prev_rank',
        'qp_country_prev_rank'  => 'qp_country_prev_rank',
        'ap_points'             => 'ap_points',
        'ap_world_rank'         => 'ap_world_rank',
        'ap_region_rank'        => 'ap_region_rank',
        'ap_server_rank'        => 'ap_server_rank',
        'ap_country_rank'       => 'ap_country_rank',
        'ap_world_trend'        => 'ap_world_trend',
        'ap_region_trend'       => 'ap_region_trend',
        'ap_server_trend'       => 'ap_server_trend',
        'ap_country_trend'      => 'ap_country_trend',
        'ap_world_prev_rank'    => 'ap_world_prev_rank',
        'ap_region_prev_rank'   => 'ap_region_prev_rank',
        'ap_server_prev_rank'   => 'ap_server_prev_rank',
        'ap_country_prev_rank'  => 'ap_country_prev_rank',
        'apf_points'            => 'apf_points',
        'apf_world_rank'        => 'apf_world_rank',
        'apf_region_rank'       => 'apf_region_rank',
        'apf_server_rank'       => 'apf_server_rank',
        'apf_country_rank'      => 'apf_country_rank',
        'apf_world_trend'       => 'apf_world_trend',
        'apf_region_trend'      => 'apf_region_trend',
        'apf_server_trend'      => 'apf_server_trend',
        'apf_country_trend'     => 'apf_country_trend',
        'apf_world_prev_rank'   => 'apf_world_prev_rank',
        'apf_region_prev_rank'  => 'apf_region_prev_rank',
        'apf_server_prev_rank'  => 'apf_server_prev_rank',
        'apf_country_prev_rank' => 'apf_country_prev_rank'
        );

    const RANKING_ENCOUNTER_MAPPER = array(
        'qp_points'        => 'qp_points',
        'qp_world_rank'    => 'qp_world_rank',
        'qp_region_rank'   => 'qp_region_rank',
        'qp_server_rank'   => 'qp_server_rank',
        'qp_country_rank'  => 'qp_country_rank',
        'ap_points'        => 'ap_points',
        'ap_world_rank'    => 'ap_world_rank',
        'ap_region_rank'   => 'ap_region_rank',
        'ap_server_rank'   => 'ap_server_rank',
        'ap_country_rank'  => 'ap_country_rank',
        'apf_points'       => 'apf_points',
        'apf_world_rank'   => 'apf_world_rank',
        'apf_region_rank'  => 'apf_region_rank',
        'apf_server_rank'  => 'apf_server_rank',
        'apf_country_rank' => 'apf_country_rank'
        );

    public static function update($guildId, $encounterId, $dungeonId) {
        // get rankings in array format, unsorted $guildId => qp_points = 'xxxxx'
        self::_getExistingRankings($encounterId, $dungeonId);

        // get dungeon details
        self::$_dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
        self::$_dungeonDetails->setClears();

        // get encounter details
        self::$_encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

        // get kill details
        foreach( self::$_dungeonDetails->_encounters as $encounter ) {
            $id = $encounter->_encounterId;

            self::$_currentEncounterStandings = DbFactory::getStandingsForEncounter($id);
            self::_setKillEntry($id);
        }

        foreach( self::$_dungeonDetails->_encounters as $encounter ) {
            $encounterId = $encounter->_encounterId;

            $encounterKillArray = DbFactory::getStandingsForEncounter($encounterId);

            foreach($encounterKillArray as $guildId => $guildDetails) {
                self::$_encounterKillTimeArray[$encounterId][$guildId] = $guildDetails->_encounterDetails->$encounterId->_strtotime;

                if ( !isset(self::$_existingEncounterRankingsArray[$guildId]) ) {
                    self::$_existingEncounterRankingsArray[$guildId] = array();
                }

                self::$_existingEncounterRankingsArray[$guildId][$encounterId] = $guildDetails;
            }
        }

        self::_generateEncounterKillDetails();

        self::_getActiveGuildsInDungeon();

        self::_sortKills();

        self::_assignPoints();

        self::_createEncounterRanksForGuilds();

        self::_createDungeonRanksForGuilds();

        self::_createEncounterInsertStrings();

        self::_createDungeonInsertStrings();
    }

    protected static function _setKillEntry($encounterId) {
        foreach( self::$_currentEncounterStandings as $encounterDetails) {
           $encounterDetails->generateEncounterDetails('encounter', $encounterId);
       }
    }

    protected static function _getExistingRankings($encounterId, $dungeonId) {
        self::$_currentQPDungeonRankings['world']  = DbFactory::getRankingsForDungeon($dungeonId, 'qp', 'world');
        self::$_currentAPDungeonRankings['world']  = DbFactory::getRankingsForDungeon($dungeonId, 'ap', 'world');
        self::$_currentAPFDungeonRankings['world'] = DbFactory::getRankingsForDungeon($dungeonId, 'apf', 'world');

        self::$_currentQPDungeonRankings['region']  = DbFactory::getRankingsForDungeon($dungeonId, 'qp', 'region');
        self::$_currentAPDungeonRankings['region']  = DbFactory::getRankingsForDungeon($dungeonId, 'ap', 'region');
        self::$_currentAPFDungeonRankings['region'] = DbFactory::getRankingsForDungeon($dungeonId, 'apf', 'region');

        self::$_currentQPDungeonRankings['server']  = DbFactory::getRankingsForDungeon($dungeonId, 'qp', 'server');
        self::$_currentAPDungeonRankings['server']  = DbFactory::getRankingsForDungeon($dungeonId, 'ap', 'server');
        self::$_currentAPFDungeonRankings['server'] = DbFactory::getRankingsForDungeon($dungeonId, 'apf', 'server');

        self::$_currentQPDungeonRankings['country'] = DbFactory::getRankingsForDungeon($dungeonId, 'qp', 'country');
        self::$_currentAPDungeonRankings['country']  = DbFactory::getRankingsForDungeon($dungeonId, 'ap', 'country');
        self::$_currentAPFDungeonRankings['country'] = DbFactory::getRankingsForDungeon($dungeonId, 'apf', 'country');
    }

    protected static function _generateEncounterKillDetails() {
        foreach( self::$_dungeonDetails->_encounters as $encounter ) {
            if ( $encounter->_type != 0 ) { unset($encounter); continue; }
            $encounter->setClears();

            // Assign Num Of Encounter Kills to Highest Kill Rate Per Dungeon
            if ( !isset(self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId]) ) {
                self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId] = $encounter->_numOfEncounterKills;
            } elseif ( $encounter->_numOfEncounterKills >= self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId] ) {
                self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId] = $encounter->_numOfEncounterKills;
            }
        }
    }

    protected static function _getActiveGuildsInDungeon() {
        foreach( self::$_dungeonDetails->_encounters as $encounter ) {
            $dungeon    = CommonDataContainer::$dungeonArray[$encounter->_dungeonId];
            $numOfKills = $encounter->_numOfEncounterKills;

            if ( !isset(self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId]) ) {
                self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId] = $numOfKills;
            } elseif ( $numOfKills >= self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId] ) {
                self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId] = $numOfKills;
            }
        }
    }

    protected static function _sortKills() {
        foreach ( self::$_encounterKillTimeArray as $encounterId => $guildArray ) {
            asort($guildArray);
            self::$_encounterKillTimeArray[$encounterId] = $guildArray;
        }
    }

    protected static function _assignPoints() {
        $dungeonFinalEncounterCheck = array();

        foreach ( self::$_encounterKillTimeArray as $encounterId => $guildArray ) {
            $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonId        = $encounterDetails->_dungeonId;
            $dungeonDetails   = CommonDataContainer::$dungeonArray[$dungeonId];

            foreach ( $guildArray as $guildId => $strtotime ) {
                $guildDetails = CommonDataContainer::$guildArray[$guildId];
                $apBaseValue  = POINT_BASE;
                $apfBaseValue = POINT_BASE_MOD;
                $qpBaseValue  = POINT_BASE;

                // Apply Special Rules 1 by 1 before generating points
                
                // Aeyths (AP) Base Value Algo
                $numOfEncounterKills = $encounterDetails->_numOfEncounterKills;
                $numOfDungeonKills   = self::$_highestNumOfKillsInDungeonArray[$dungeonDetails->_dungeonId];
                $numOfActiveGuilds   = self::$_numOfGuildsActiveInDungeonArray[$dungeonDetails->_dungeonId];
                $apBaseValue         = number_format( ( ($numOfActiveGuilds / $numOfEncounterKills) * $apBaseValue ), 0, '', '');

                // Quality Progression (QP) Final Encounter Bonus Base
                if ( $dungeonDetails->_finalEncounterId == $encounterId ) {
                    $qpBaseValue = POINT_FINAL_BASE * $dungeonDetails->_numOfEncounters;
                }

                $firstKillTime     = reset($guildArray);
                $killTimeDiff      = $firstKillTime - $strtotime;
                $timeDiffTotalDays = $killTimeDiff/(60 * 60 * 24);
                $expValue          = $timeDiffTotalDays / 50;

                // Generate Points
                $apValue  = ($numOfActiveGuilds / $numOfEncounterKills) * $apBaseValue * exp($expValue);
                $apfValue = $apfBaseValue * exp($expValue);
                $qpValue  = $qpBaseValue * exp($expValue);

                // Add Quality Progression Final Encounter Bonus
                if ( $dungeonDetails->_finalEncounterId == $encounterId ) {
                    $qpValue += (POINT_BASE * $dungeonDetails->_numOfEncounters);
                }

                self::$_encounterPointArray[$encounterId]['QP'][$guildId]  = $qpValue . '_QP';
                self::$_encounterPointArray[$encounterId]['AP'][$guildId]  = $apValue . '_AP';
                self::$_encounterPointArray[$encounterId]['APF'][$guildId] = $apfValue . '_APF';

                // Assign Points to Dungeons

                // Quality Progression
                if ( !isset(self::$_dungeonPointArray[$dungeonId]['QP'][$guildId]) ) {
                    self::$_dungeonPointArray[$dungeonId]['QP'][$guildId]  = $qpValue . '_QP';
                } else {
                    $pointValues = explode('_', self::$_dungeonPointArray[$dungeonId]['QP'][$guildId]);

                    // If Final Encounter, make point value the final dungeon value
                    if ( $dungeonDetails->_finalEncounterId == $encounterId && !isset($dungeonFinalEncounterCheck[$dungeonId][$guildId] ) ) {
                        self::$_dungeonPointArray[$dungeonId]['QP'][$guildId]  = $qpValue . '_' . $pointValues[1];
                        $dungeonFinalEncounterCheck[$dungeonId][$guildId] = true;
                    } else {
                        self::$_dungeonPointArray[$dungeonId]['QP'][$guildId]  = ($qpValue + $pointValues[0]) . '_' . $pointValues[1];
                    }
                }

                // Aeyths Point
                if ( !isset(self::$_dungeonPointArray[$dungeonId]['AP'][$guildId]) ) {
                    self::$_dungeonPointArray[$dungeonId]['AP'][$guildId]  = $apValue . '_AP';
                } else {
                    $pointValues = explode('_', self::$_dungeonPointArray[$dungeonId]['AP'][$guildId]);
                    self::$_dungeonPointArray[$dungeonId]['AP'][$guildId]  = ($apValue + $pointValues[0]) . '_' . $pointValues[1];
                }

                // Aeyths Point Flat
                if ( !isset(self::$_dungeonPointArray[$dungeonId]['APF'][$guildId]) ) {
                    self::$_dungeonPointArray[$dungeonId]['APF'][$guildId]  = $apfValue . '_APF';
                } else {
                    $pointValues = explode('_', self::$_dungeonPointArray[$dungeonId]['APF'][$guildId]);
                    self::$_dungeonPointArray[$dungeonId]['APF'][$guildId]  = ($apfValue + $pointValues[0]) . '_' . $pointValues[1];
                }
            }
        }
    }

    protected static function _createEncounterRanksForGuilds() {
        ksort(self::$_encounterPointArray);
        foreach ( self::$_encounterPointArray as $encounterId => $rankSystemId ) {
            $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonDetails   = CommonDataContainer::$dungeonArray[$encounterDetails->_dungeonId];

            foreach ( $rankSystemId as $guildArray ) {
                $encounterRank = array();

                $pointArray = array();
                foreach ( $guildArray as $guildId => $pointValue ) {
                    $pointValue   = explode('_', $pointValue);
                    $rankSystem   = $pointValue[1];
                    $points       = $pointValue[0];
                    $pointArray[$guildId] = $points;
                }

                foreach ( $pointArray as $guildId => $pointValue ) {
                    $guildDetails = CommonDataContainer::$guildArray[$guildId];
                    $server       = $guildDetails->_server;
                    $region       = $guildDetails->_region;
                    $country      = $guildDetails->_country;
                    $pointValue   = explode('_', $guildArray[$guildId]);
                    $rankSystem   = $pointValue[1];
                    $points       = $pointValue[0];

                    if ( !isset($encounterRank['world']) ) { $encounterRank['world'] = 0; }
                    if ( !isset($encounterRank['server'][$server]) ) { $encounterRank['server'][$server] = 0; }
                    if ( !isset($encounterRank['region'][$region]) ) { $encounterRank['region'][$region] = 0; }
                    if ( !isset($encounterRank['country'][$country]) ) { $encounterRank['country'][$country] = 0; }

                    $encounterRank['world']++;
                    $encounterRank['server'][$server]++;
                    $encounterRank['region'][$region]++;
                    $encounterRank['country'][$country]++;

                    $rankValue = $rankSystem. '||' . 
                                 $points . '||' . 
                                 $encounterRank['world'] . '||' . 
                                 $encounterRank['server'][$server] . '||' . 
                                 $encounterRank['region'][$region] . '||' . 
                                 $encounterRank['country'][$country];

                    $encounterIdentifier = $encounterId . '_' . $rankSystem;

                    self::$_rankArrayForEncounters[$guildId][$encounterIdentifier] = $rankValue;
                }
            }
        }
    }

    protected static function _createDungeonRanksForGuilds() {
        ksort(self::$_dungeonPointArray);
        foreach ( self::$_dungeonPointArray as $dungeonId => $rankSystemId ) {
            $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

            foreach ( $rankSystemId as $guildArray ) {
                $dungeonRank = array();

                $pointArray = array();
                foreach ( $guildArray as $guildId => $pointValue ) {
                    $pointValue   = explode('_', $pointValue);
                    $rankSystem   = $pointValue[1];
                    $points       = $pointValue[0];
                    $pointArray[$guildId] = $points;
                }

                arsort($pointArray);
                foreach ( $pointArray as $guildId => $points ) {
                    $guildDetails = CommonDataContainer::$guildArray[$guildId];
                    $server       = $guildDetails->_server;
                    $region       = $guildDetails->_region;
                    $country      = $guildDetails->_country;
                    $pointValue   = explode('_', $guildArray[$guildId]);
                    $rankSystem   = $pointValue[1];
                    $points       = $pointValue[0];

                    if ( !isset($dungeonRank['world']) ) { $dungeonRank['world'] = 0; }
                    if ( !isset($dungeonRank['server'][$server]) ) { $dungeonRank['server'][$server] = 0; }
                    if ( !isset($dungeonRank['region'][$region]) ) { $dungeonRank['region'][$region] = 0; }
                    if ( !isset($dungeonRank['country'][$country]) ) { $dungeonRank['country'][$country] = 0; }

                    $dungeonRank['world']++;
                    $dungeonRank['server'][$server]++;
                    $dungeonRank['region'][$region]++;
                    $dungeonRank['country'][$country]++;

                    $dungeonTrend = self::_getTrends($guildDetails, $dungeonRank, $rankSystem, $dungeonId);

                    $rankValue = $rankSystem. '||' . 
                                 $points . '||' . 
                                 $dungeonRank['world'] . '||' . 
                                 $dungeonRank['server'][$server] . '||' . 
                                 $dungeonRank['region'][$region] . '||' . 
                                 $dungeonRank['country'][$country] . '||' . 
                                 $dungeonTrend['trend']['world'] . '||' . 
                                 $dungeonTrend['trend']['server'][$server] . '||' . 
                                 $dungeonTrend['trend']['region'][$region] . '||' . 
                                 $dungeonTrend['trend']['country'][$country] . '||' . 
                                 $dungeonTrend['prev-rank']['world'] . '||' . 
                                 $dungeonTrend['prev-rank']['server'][$server] . '||' . 
                                 $dungeonTrend['prev-rank']['region'][$region] . '||' . 
                                 $dungeonTrend['prev-rank']['country'][$country];

                    $dungeonIdentifier = $dungeonId . '_' . $rankSystem;

                    self::$_rankArrayForDungeons[$guildId][$dungeonIdentifier] = $rankValue;
                }
            }
        }
    }

    protected static function _getTrends($guildDetails, $rankDetails, $rankSystem, $dungeonId) {
        $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
        $guildId        = $guildDetails->_guildId;
        $server         = $guildDetails->_server;
        $region         = $guildDetails->_region;
        $country        = $guildDetails->_country;
        $identifier     = '_' . strtolower($rankSystem);
        $trendDetails   = array();

        $newWorldRank   = $rankDetails['world'];
        $newServerRank  = $rankDetails['server'][$server];
        $newRegionRank  = $rankDetails['region'][$region];
        $newCountryRank = $rankDetails['country'][$country];

        $worldRankValues;
        $regionRankValues;
        $serverRankValues;
        $countryRankValues;

        if ( strtolower($rankSystem) == 'qp' && isset(self::$_currentQPDungeonRankings['world'][$guildId]) ) {
            $worldRankValues   = self::$_currentQPDungeonRankings['world'][$guildId];
            $regionRankValues  = self::$_currentQPDungeonRankings['region'][$guildId];
            $serverRankValues  = self::$_currentQPDungeonRankings['server'][$guildId];
            $countryRankValues = self::$_currentQPDungeonRankings['country'][$guildId];
        } elseif ( strtolower($rankSystem) == 'ap' && isset(self::$_currentAPDungeonRankings['world'][$guildId]) ) {
            $worldRankValues   = self::$_currentAPDungeonRankings['world'][$guildId];
            $regionRankValues  = self::$_currentAPDungeonRankings['region'][$guildId];
            $serverRankValues  = self::$_currentAPDungeonRankings['server'][$guildId];
            $countryRankValues = self::$_currentAPDungeonRankings['country'][$guildId];
        } elseif ( strtolower($rankSystem) == 'apf' && isset(self::$_currentAPFDungeonRankings['world'][$guildId]) ) {
            $worldRankValues   = self::$_currentAPFDungeonRankings['world'][$guildId];
            $regionRankValues  = self::$_currentAPFDungeonRankings['region'][$guildId];
            $serverRankValues  = self::$_currentAPFDungeonRankings['server'][$guildId];
            $countryRankValues = self::$_currentAPFDungeonRankings['country'][$guildId];
        }

        if ( !empty($worldRankValues) ) {
            $currentWorldRank   = $worldRankValues->_rank;
            $currentRegionRank  = $regionRankValues->_rank;
            $currentServerRank  = $serverRankValues->_rank;
            $currentCountryRank = $countryRankValues->_rank;

            $currentWorldTrend   = ( !empty($worldRankValues->_trend) ? $worldRankValues->_trend : '--' );
            $currentRegionTrend  = ( !empty($regionRankValues->_trend) ? $regionRankValues->_trend : '--' );
            $currentServerTrend  = ( !empty($serverRankValues->_trend) ? $serverRankValues->_trend : '--' );
            $currentCountryTrend = ( !empty($countryRankValues->_trend) ? $countryRankValues->_trend : '--' );

            // Rank went down Current Rank 1 New Rank 5, Trend = -4
            if ( $newWorldRank > $currentWorldRank ) { $trendDetails['trend']['world'] = -1 * ($newWorldRank - $currentWorldRank); }
            if ( $newRegionRank > $currentRegionRank ) { $trendDetails['trend']['region'][$region] = -1 * ($newRegionRank - $currentRegionRank); }
            if ( $newServerRank > $currentServerRank ) { $trendDetails['trend']['server'][$server] = -1 * ($newServerRank - $currentServerRank); }
            if ( $newCountryRank > $currentCountryRank ) { $trendDetails['trend']['country'][$country] = -1 * ($newCountryRank - $currentCountryRank); }

            // Rank went up Current Rank 5 New Rank 1, Trend = +4
            if ( $newWorldRank < $currentWorldRank ) { $trendDetails['trend']['world'] = $currentWorldRank - $newWorldRank; }
            if ( $newRegionRank < $currentRegionRank ) { $trendDetails['trend']['region'][$region] = $currentRegionRank - $newRegionRank; }
            if ( $newServerRank < $currentServerRank ) { $trendDetails['trend']['server'][$server] = $currentServerRank - $newServerRank; }
            if ( $newCountryRank < $currentCountryRank ) { $trendDetails['trend']['country'][$country] = $currentCountryRank - $newCountryRank; }

            // Rank did not change
            if ( $newWorldRank == $currentWorldRank ) { $trendDetails['trend']['world'] = '--'; }
            if ( $newRegionRank == $currentRegionRank ) { $trendDetails['trend']['region'][$region] = '--'; }
            if ( $newServerRank == $currentServerRank ) { $trendDetails['trend']['server'][$server] = '--'; }
            if ( $newCountryRank == $currentCountryRank ) { $trendDetails['trend']['country'][$country] = '--'; }

            // Prev Rank
            $trendDetails['prev-rank']['world']             = $currentWorldRank;
            $trendDetails['prev-rank']['region'][$region]   = $currentRegionRank;
            $trendDetails['prev-rank']['server'][$server]   = $currentServerRank;
            $trendDetails['prev-rank']['country'][$country] = $newCountryRank;
        } else {
            // Rank is New
            $trendDetails['trend']['world']             = 'NEW';
            $trendDetails['trend']['region'][$region]   = 'NEW';
            $trendDetails['trend']['server'][$server]   = 'NEW';
            $trendDetails['trend']['country'][$country] = 'NEW';

            // Prev Rank is -- as there is no previous rank
            $trendDetails['prev-rank']['world']             = '--';
            $trendDetails['prev-rank']['region'][$region]   = '--';
            $trendDetails['prev-rank']['server'][$server]   = '--';
            $trendDetails['prev-rank']['country'][$country] = '--';
        }

       return $trendDetails;
    }

    protected static function _createDungeonInsertStrings() {
        $dbh = DbFactory::getDbh();

        ksort(self::$_rankArrayForDungeons);

        /*
        $insertSQL = sprintf("INSERT INTO %s
                             (guild_id,
                              dungeon_id,
                              progress,
                              special_progress,
                              qp_points,
                              qp_world_rank,
                              qp_region_rank,
                              qp_server_rank,
                              qp_country_rank,
                              qp_world_trend,
                              qp_region_trend,
                              qp_server_trend,
                              qp_country_trend,
                              qp_world_prev_rank,
                              qp_region_prev_rank,
                              qp_server_prev_rank,
                              qp_country_prev_rank,
                              ap_points,
                              ap_world_rank,
                              ap_region_rank,
                              ap_server_rank,
                              ap_country_rank,
                              ap_world_trend,
                              ap_region_trend,
                              ap_server_trend,
                              ap_country_trend,
                              ap_world_prev_rank,
                              ap_region_prev_rank,
                              ap_server_prev_rank,
                              ap_country_prev_rank,
                              apf_points,
                              apf_world_rank,
                              apf_region_rank,
                              apf_server_rank,
                              apf_country_rank,
                              apf_world_trend,
                              apf_region_trend,
                              apf_server_trend,
                              apf_country_trend,
                              apf_world_prev_rank,
                              apf_region_prev_rank,
                              apf_server_prev_rank,
                              apf_country_prev_rank)
                              values", DbFactory::TABLE_RANKINGS);
        */

        $insertQPSQL = sprintf("INSERT INTO %s
                             (guild_id,
                              dungeon_id,
                              progress,
                              special_progress,
                              qp_points,
                              qp_world_rank,
                              qp_region_rank,
                              qp_server_rank,
                              qp_country_rank,
                              qp_world_trend,
                              qp_region_trend,
                              qp_server_trend,
                              qp_country_trend,
                              qp_world_prev_rank,
                              qp_region_prev_rank,
                              qp_server_prev_rank,
                              qp_country_prev_rank)
                              values", DbFactory::TABLE_RANKINGS);

        $insertAPSQL = sprintf("INSERT INTO %s
                             (guild_id,
                              dungeon_id,
                              progress,
                              special_progress,
                              ap_points,
                              ap_world_rank,
                              ap_region_rank,
                              ap_server_rank,
                              ap_country_rank,
                              ap_world_trend,
                              ap_region_trend,
                              ap_server_trend,
                              ap_country_trend,
                              ap_world_prev_rank,
                              ap_region_prev_rank,
                              ap_server_prev_rank,
                              ap_country_prev_rank)
                              values", DbFactory::TABLE_RANKINGS);

        $insertAPFSQL = sprintf("INSERT INTO %s
                             (guild_id,
                              dungeon_id,
                              progress,
                              special_progress,
                              apf_points,
                              apf_world_rank,
                              apf_region_rank,
                              apf_server_rank,
                              apf_country_rank,
                              apf_world_trend,
                              apf_region_trend,
                              apf_server_trend,
                              apf_country_trend,
                              apf_world_prev_rank,
                              apf_region_prev_rank,
                              apf_server_prev_rank,
                              apf_country_prev_rank)
                              values", DbFactory::TABLE_RANKINGS);

        $insertValueSQL = '';
        $updateSQL      = '';

        $updateQPSQL       = '';
        $updateAPSQL       = '';
        $updateAPFSQL      = '';
        $insertQPValueSQL  = '';
        $insertAPValueSQL  = '';
        $insertAPFValueSQL = '';

        $dungeonDetailsArray = array();

        foreach ( self::$_rankArrayForDungeons as $guildId => $dungeonArray ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $detailsArray = array();

            if ( !isset($dungeonDetailsArray[$guildId]) ) {
                $dungeonDetailsArray[$guildId] = array();
            }

            foreach ( $dungeonArray as $dungeon => $rankValue ) {
                $dungeonVals    = explode('_', $dungeon);
                $rankValue      = explode('||', $rankValue);
                $dungeonId      = $dungeonVals[0];

                if ( $dungeonId != self::$_dungeonDetails->_dungeonId ) { continue; }

                $system         = strtolower($dungeonVals[1]);
                $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

                // new rankings table code
                if ( !isset($detailsArray[$dungeonId]) ) {
                    $standingsDetails                             = DbFactory::getStandingsForGuildInDungeon($dungeonId, $guildId);
                    $detailsArray[$dungeonId]                     = array();
                    $detailsArray[$dungeonId]['guild_id']         = $guildId;
                    $detailsArray[$dungeonId]['dungeon_id']       = $dungeonId;
                    $detailsArray[$dungeonId]['progress']         = $standingsDetails->_progress;
                    $detailsArray[$dungeonId]['special_progress'] = $standingsDetails->_specialProgress;
                }

                $detailsArray[$dungeonId][$system . '_points']            = $rankValue[1];
                $detailsArray[$dungeonId][$system . '_world_rank']        = $rankValue[2];
                $detailsArray[$dungeonId][$system . '_server_rank']       = $rankValue[3];
                $detailsArray[$dungeonId][$system . '_region_rank']       = $rankValue[4];
                $detailsArray[$dungeonId][$system . '_country_rank']      = $rankValue[5];
                $detailsArray[$dungeonId][$system . '_world_trend']       = $rankValue[6];
                $detailsArray[$dungeonId][$system . '_server_trend']      = $rankValue[7];
                $detailsArray[$dungeonId][$system . '_region_trend']      = $rankValue[8];
                $detailsArray[$dungeonId][$system . '_country_trend']     = $rankValue[9];
                $detailsArray[$dungeonId][$system . '_world_prev_rank']   = $rankValue[10];
                $detailsArray[$dungeonId][$system . '_server_prev_rank']  = $rankValue[11];
                $detailsArray[$dungeonId][$system . '_region_prev_rank']  = $rankValue[12];
                $detailsArray[$dungeonId][$system . '_country_prev_rank'] = $rankValue[13];
            }

            $dungeonDetailsArray[$guildId] = $detailsArray;
        }

        // handle insert
        foreach ( $dungeonDetailsArray as $guildId => $detailsArray ) {
            foreach ( $detailsArray as $dungeonId => $dungeons ) {
                $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

                if ( !empty($insertValueSQL) ) {
                    $insertValueSQL .= ',';
                }

                if ( !empty($insertQPValueSQL) ) {
                    $insertQPValueSQL .= ',';
                }

                if ( !empty($insertAPValueSQL) ) {
                    $insertAPValueSQL .= ',';
                }

                if ( !empty($insertAPFValueSQL) ) {
                    $insertAPFValueSQL .= ',';
                }

                $insertValueSQL  .= '(';
                $dungeonValueSQL =  $guildId . ', ' . $dungeonId;


                $insertQPValueSQL   .= '(';
                $insertAPValueSQL   .= '(';
                $insertAPFValueSQL  .= '(';
                $dungeonValueQPSQL  =  $guildId . ', ' . $dungeonId;
                $dungeonValueAPSQL  =  $guildId . ', ' . $dungeonId;
                $dungeonValueAPFSQL =  $guildId . ', ' . $dungeonId;

                /*
                foreach ( self::RANKING_MAPPER as $columnName => $value ) {
                    if ( !empty($dungeonValueSQL) ) {
                        $dungeonValueSQL .= ',';
                    }

                    $dungeonValueSQL .= "'" .  $detailsArray[$dungeonId][$value] . "'";
                }

                $insertValueSQL .= $dungeonValueSQL . ')';
                */

                // QP
                foreach ( self::RANKING_QP_MAPPER as $columnName => $value ) {
                    if ( !empty($dungeonValueQPSQL) ) {
                        $dungeonValueQPSQL .= ',';
                    }

                    $dungeonValueQPSQL .= "'" .  $detailsArray[$dungeonId][$value] . "'";
                }

                $insertQPValueSQL .= $dungeonValueQPSQL . ')';

                // AP
                foreach ( self::RANKING_AP_MAPPER as $columnName => $value ) {
                    if ( !empty($dungeonValueAPSQL) ) {
                        $dungeonValueAPSQL .= ',';
                    }

                    $dungeonValueAPSQL .= "'" .  $detailsArray[$dungeonId][$value] . "'";
                }

                $insertAPValueSQL .= $dungeonValueAPSQL . ')';

                // APF
                foreach ( self::RANKING_APF_MAPPER as $columnName => $value ) {
                    if ( !empty($dungeonValueAPFSQL) ) {
                        $dungeonValueAPFSQL .= ',';
                    }

                    $dungeonValueAPFSQL .= "'" .  $detailsArray[$dungeonId][$value] . "'";
                }

                $insertAPFValueSQL .= $dungeonValueAPFSQL . ')';
            }
        }

        // handle update
        /*
        foreach ( self::RANKING_MAPPER as $columnName => $value ) {
            if ( !empty($updateSQL) ) {
                $updateSQL .= ',';
            }

            $updateSQL .= ' ' . $columnName . ' = CASE';

            foreach ( $dungeonDetailsArray as $guildId => $detailsArray ) {
                foreach ( $detailsArray as $dungeonId => $dungeons ) {
                    foreach ( $dungeons as $key => $detailValue ) {
                        $updateSQL .= " WHEN guild_id = '" . $guildId . "' AND dungeon_id = '" . $dungeonId . "' THEN '" . $dungeons[$value] . "'";
                    }
                }
            }

            $updateSQL .= ' END';
        }
        */
        // QP
        foreach ( self::RANKING_QP_MAPPER as $columnName => $value ) {
            if ( !empty($updateQPSQL) ) {
                $updateQPSQL .= ',';
            }

            $updateQPSQL .= ' ' . $columnName . ' = CASE';

            foreach ( $dungeonDetailsArray as $guildId => $detailsArray ) {
                foreach ( $detailsArray as $dungeonId => $dungeons ) {
                    $updateQPSQL .= " WHEN guild_id = '" . $guildId . "' AND dungeon_id = '" . $dungeonId . "' THEN '" . $dungeons[$value] . "'";
                }
            }

            $updateQPSQL .= ' END';
        }

        // AP
        foreach ( self::RANKING_AP_MAPPER as $columnName => $value ) {
            if ( !empty($updateAPSQL) ) {
                $updateAPSQL .= ',';
            }

            $updateAPSQL .= ' ' . $columnName . ' = CASE';

            foreach ( $dungeonDetailsArray as $guildId => $detailsArray ) {
                foreach ( $detailsArray as $dungeonId => $dungeons ) {
                    $updateAPSQL .= " WHEN guild_id = '" . $guildId . "' AND dungeon_id = '" . $dungeonId . "' THEN '" . $dungeons[$value] . "'";
                }
            }

            $updateAPSQL .= ' END';
        }

        // APF
        foreach ( self::RANKING_APF_MAPPER as $columnName => $value ) {
            if ( !empty($updateAPFSQL) ) {
                $updateAPFSQL .= ',';
            }

            $updateAPFSQL .= ' ' . $columnName . ' = CASE';

            foreach ( $dungeonDetailsArray as $guildId => $detailsArray ) {
                foreach ( $detailsArray as $dungeonId => $dungeons ) {
                    $updateAPFSQL .= " WHEN guild_id = '" . $guildId . "' AND dungeon_id = '" . $dungeonId . "' THEN '" . $dungeons[$value] . "'";
                }
            }

            $updateAPFSQL .= ' END';
        }

        /*
        $insertSQL = $insertSQL . ' ' . $insertValueSQL;
        $insertSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateSQL;
        $query = $dbh->prepare($insertSQL);
        $query->execute();
        */

        $insertQPSQL = $insertQPSQL . ' ' . $insertQPValueSQL;
        $insertQPSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateQPSQL;
        $query = $dbh->prepare($insertQPSQL);
        $query->execute();

        $insertAPSQL = $insertAPSQL . ' ' . $insertAPValueSQL;
        $insertAPSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateAPSQL;
        $query = $dbh->prepare($insertAPSQL);
        $query->execute();

        $insertAPFSQL = $insertAPFSQL . ' ' . $insertAPFValueSQL;
        $insertAPFSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateAPFSQL;
        $query = $dbh->prepare($insertAPFSQL);
        $query->execute();
    }

    protected static function _createEncounterInsertStrings() {
        $dbh = DbFactory::getDbh();

        ksort(self::$_rankArrayForEncounters);

        $insertSQL = sprintf("INSERT INTO %s
                            (guild_id,
                             encounter_id,
                             dungeon_id,
                             qp_points,
                             qp_world_rank,
                             qp_region_rank,
                             qp_server_rank,
                             qp_country_rank,
                             ap_points,
                             ap_world_rank,
                             ap_region_rank,
                             ap_server_rank,
                             ap_country_rank,
                             apf_points,
                             apf_world_rank,
                             apf_region_rank,
                             apf_server_rank,
                             apf_country_rank)
                             values", DbFactory::TABLE_ENCOUNTER_RANKINGS);
        $insertValueSQL = '';
        $updateSQL      = '';

        foreach ( self::$_rankArrayForEncounters as $guildId => $encounterArray ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $detailsArray = array();

            foreach ( $encounterArray as $encounter => $rankValue ) {
                $encounterVals    = explode('_', $encounter);
                $rankValue        = explode('||', $rankValue);
                $encounterId      = $encounterVals[0];

                if ( $encounterId != self::$_encounterDetails->_encounterId ) { continue; }

                $system           = strtolower($encounterVals[1]);
                $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

                // new rankings table code
                if ( empty($detailsArray[$encounterId]) ) {
                    $detailsArray[$encounterId]                 = array();
                    $detailsArray[$encounterId]['guild_id']     = $guildId;
                    $detailsArray[$encounterId]['encounter_id'] = $encounterId;
                    $detailsArray[$encounterId]['dungeon_id']   = $encounterDetails->_dungeonId;
                }

                $detailsArray[$encounterId][$system . '_points']       = $rankValue[1];
                $detailsArray[$encounterId][$system . '_world_rank']   = $rankValue[2];
                $detailsArray[$encounterId][$system . '_server_rank']  = $rankValue[3];
                $detailsArray[$encounterId][$system . '_region_rank']  = $rankValue[4];
                $detailsArray[$encounterId][$system . '_country_rank'] = $rankValue[5];
            }

            // handle insert
            foreach ( $detailsArray as $encounterId => $encounters ) {
                $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

                if ( !empty($insertValueSQL) ) {
                    $insertValueSQL .= ',';
                }

                $insertValueSQL    .= '(';
                $encounterValueSQL =  $guildId . ', ' . $encounterId . ', ' . $encounterDetails->_dungeonId;

                foreach ( self::RANKING_ENCOUNTER_MAPPER as $columnName => $value ) {
                    if ( !empty($encounterValueSQL) ) {
                        $encounterValueSQL .= ',';
                    }

                    $encounterValueSQL .= "'" .  $detailsArray[$encounterId][$value] . "'";
                }

                $insertValueSQL .= $encounterValueSQL . ')';
            }

            // handle update
            foreach ( self::RANKING_ENCOUNTER_MAPPER as $columnName => $value ) {
                if ( !empty($updateSQL) ) {
                    $updateSQL .= ',';
                }

                $updateSQL .= ' ' . $columnName . ' = CASE';

                foreach ( $detailsArray as $encounterId => $encounters ) {
                    foreach ( $encounters as $key => $detailValue ) {
                        $updateSQL .= " WHEN guild_id = '" . $guildId . "' AND encounter_id = '" . $encounterId . "' THEN '" . $encounters[$value] . "'";
                    }
                }

                $updateSQL .= ' END';
            }
        }

        $insertSQL = $insertSQL . ' ' . $insertValueSQL;
        $insertSQL .= ' ON DUPLICATE KEY UPDATE ' . $updateSQL;
        $query = $dbh->prepare($insertSQL);
        $query->execute();
    }
}