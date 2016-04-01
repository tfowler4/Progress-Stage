<?php

/**
 * rankings object to handle the creation and updating process of guild dungeon/encounter rankings
 */
class Rankings {
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
    protected static $_dungeonDetails;

	public static function create() {

	}

	public static function update($guildId, $encounterId, $dungeonId) {
        // get rankings in array format, unsorted $guildId => qp_points = 'xxxxx'
		self::_getExistingRankings($encounterId, $dungeonId);

        // get dungeon details
        self::$_dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
        self::$_dungeonDetails->setClears();

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
            }
        }

        self::_generateEncounterKillDetails();

        self::_getActiveGuildsInDungeon();

        self::_sortKills();

        self::_assignPoints();

        self::_createEncounterRanksForGuilds();

        self::_createDungeonRanksForGuilds();

        //print_r(self::$_rankArrayForDungeons);

        // checking performance
        echo ' <br>Load Time: '.(round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"])/1, 2)).' seconds.'; 
        echo ' <br>Memory Usage: ' .round(memory_get_usage(true)/1048576,2) . 'mb';
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

        $rankValues;

        if ( $rankSystem == 'qp' && self::$_currentQPDungeonRankings['world'][$guildId] ) {
            $rankValues = self::$_currentQPDungeonRankings[$guildId];
        } elseif ( $rankSystem == 'ap' && self::$_currentAPDungeonRankings['world'][$guildId] ) {
            $rankValues = self::$_currentAPDungeonRankings[$guildId];
        } elseif ( $rankSystem == 'apf' && self::$_currentAPFDungeonRankings['world'][$guildId] ) {
            $rankValues = self::$_currentAPFDungeonRankings[$guildId];
        }

        if ( !empty($rankValues) ) {

        //if ( isset($guildDetails->_dungeonDetails->$dungeonId->$identifier) ) {
            //$rankValues = $guildDetails->_dungeonDetails->$dungeonId->$identifier;

            $currentWorldRank   = $rankValues->_rank->_world;
            $currentServerRank  = $rankValues->_rank->_server;
            $currentRegionRank  = $rankValues->_rank->_region;
            $currentCountryRank = $rankValues->_rank->_country;

            $currentWorldTrend   = ( !empty($rankValues->_trend->_world) ? $rankValues->_trend->_world : '--' );
            $currentServerTrend  = ( !empty($rankValues->_trend->_server) ? $rankValues->_trend->_server : '--' );
            $currentRegionTrend  = ( !empty($rankValues->_trend->_region) ? $rankValues->_trend->_region : '--' );
            $currentCountryTrend = ( !empty($rankValues->_trend->_country) ? $rankValues->_trend->_country : '--' );

            // Rank went down Current Rank 1 New Rank 5, Trend = -4
            if ( $newWorldRank > $currentWorldRank ) { $trendDetails['trend']['world'] = -1 * ($newWorldRank - $currentWorldRank); }
            if ( $newServerRank > $currentServerRank ) { $trendDetails['trend']['server'][$server] = -1 * ($newServerRank - $currentServerRank); }
            if ( $newRegionRank > $currentRegionRank ) { $trendDetails['trend']['region'][$region] = -1 * ($newRegionRank - $currentRegionRank); }
            if ( $newCountryRank > $currentCountryRank ) { $trendDetails['trend']['country'][$country] = -1 * ($newCountryRank - $currentCountryRank); }

            // Rank went up Current Rank 5 New Rank 1, Trend = +4
            if ( $newWorldRank < $currentWorldRank ) { $trendDetails['trend']['world'] = $currentWorldRank - $newWorldRank; }
            if ( $newServerRank < $currentServerRank ) { $trendDetails['trend']['server'][$server] = $currentServerRank - $newServerRank; }
            if ( $newRegionRank < $currentRegionRank ) { $trendDetails['trend']['region'][$region] = $currentRegionRank - $newRegionRank; }
            if ( $newCountryRank < $currentCountryRank ) { $trendDetails['trend']['country'][$country] = $currentCountryRank - $newCountryRank; }

            // Rank did not change
            if ( $newWorldRank == $currentWorldRank ) { $trendDetails['trend']['world'] = '--'; }
            if ( $newServerRank == $currentServerRank ) { $trendDetails['trend']['server'][$server] = '--'; }
            if ( $newRegionRank == $currentRegionRank ) { $trendDetails['trend']['region'][$region] = '--'; }
            if ( $newCountryRank == $currentCountryRank ) { $trendDetails['trend']['country'][$country] = '--'; }

            // Prev Rank
            $trendDetails['prev-rank']['world']             = $currentWorldRank;
            $trendDetails['prev-rank']['server'][$server]   = $currentServerRank;
            $trendDetails['prev-rank']['region'][$region]   = $currentRegionRank;
            $trendDetails['prev-rank']['country'][$country] = $newCountryRank;
        } else {
            // Rank is New
            $trendDetails['trend']['world']             = 'NEW';
            $trendDetails['trend']['server'][$server]   = 'NEW';
            $trendDetails['trend']['region'][$region]   = 'NEW';
            $trendDetails['trend']['country'][$country] = 'NEW';

            // Prev Rank is -- as there is no previous rank
            $trendDetails['prev-rank']['world']             = '--';
            $trendDetails['prev-rank']['server'][$server]   = '--';
            $trendDetails['prev-rank']['region'][$region]   = '--';
            $trendDetails['prev-rank']['country'][$country] = '--';
        }

       return $trendDetails;
    }
}