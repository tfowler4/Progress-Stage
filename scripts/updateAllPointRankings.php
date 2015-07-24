<?php

include_once 'script.php';

class UpdateAllPointRankings extends Script {
    protected static $_encountersNeedUpdateArray;
    protected static $_encounterKillTimeArray;
    protected static $_encounterPointArray;
    protected static $_dungeonPointArray;
    protected static $_highestNumOfKillsInDungeonArray;
    protected static $_numOfGuildsActiveInDungeonArray;
    protected static $_rankArrayForEncounters;
    protected static $_rankArrayForDungeons;

    public static function init() {
        Logger::log('INFO', 'Starting Update All Point Rankings...');

        Logger::log('INFO', 'Encounters Needing Updates');
        self::getEncountersNeedingUpdate();

        Logger::log('INFO', 'Generating Guild Details');
        self::generateGuildEncounterDetails();

        Logger::log('INFO', 'Generate Encounter Kill Details');
        self::generateEncounterKillDetails();

        Logger::log('INFO', 'Generate Dungeon Kill Details');
        self::generateDungeonKillDetails();

        Logger::log('INFO', 'Put Guild Kills Into Array');
        self::putGuildKillsInArray();

        Logger::log('INFO', 'Get Active Guilds in Dungeon');
        self::getActiveGuildsInDungeon();

        Logger::log('INFO', 'Sort Kills By Time Per Encounter');
        self::sortKills();

        Logger::log('INFO', 'Assign Points To Encounters');
        self::assignPoints();

        Logger::log('INFO', 'Create Encounter Ranks For Guilds');
        self::createEncounterRanksForGuilds();

        Logger::log('INFO', 'Create Dungeon Ranks And Trends For Guilds');
        self::createDungeonRanksForGuilds();

        Logger::log('INFO', 'Create Encounter Database Insert Strings');
        self::createEncounterInsertStrings();

        Logger::log('INFO', 'Create Dungeon Database Insert Strings');
        self::createDungeonInsertStrings();

        Logger::log('INFO', 'Updating Recent Raid Table');
        self::updateRecentRaidTable();

        Logger::log('INFO', 'Update All Point Rankings Completed!');
    }

    public static function updateRecentRaidTable() {
        $query = self::$_dbh->query(sprintf(
            "UPDATE %s
                SET update_rank = 1",
            DbFactory::TABLE_RECENT_RAIDS
            ));
        $query->execute();
    }

    public static function createDungeonInsertStrings() {
        ksort(self::$_rankArrayForDungeons);
        foreach ( self::$_rankArrayForDungeons as $guildId => $dungeonArray ) {
            $guildDetails             = CommonDataContainer::$guildArray[$guildId];
            $dungeonInsertStringArray = array();

            foreach ( $dungeonArray as $dungeon => $rankValue ) {
                $dungeon        = explode('_', $dungeon);
                $rankValue      = explode('||', $rankValue);
                $dungeonId      = $dungeon[0];
                $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

                if ( !isset($dungeonInsertStringArray[$dungeonId]) ) {
                    $dungeonInsertStringArray[$dungeonId] = $dungeonId . '<>' . implode('||', $rankValue);
                } else {
                    $dungeonInsertStringArray[$dungeonId] .= '++' . implode('||', $rankValue);
                }
            }

            $dbInsertString = '';

            foreach ($dungeonInsertStringArray as $dungeonId => $insertString ) {
                if ( empty($dbInsertString) ) {
                    $dbInsertString = $insertString;
                } else {
                    $dbInsertString .= '~~' .  $insertString;
                }
            }

            $query = self::$_dbh->query(sprintf(
                "UPDATE guild_table
                    SET rank_dungeon = '%s'
                  WHERE guild_id = '%s'",
                $dbInsertString,
                $guildId
                ));
            $query->execute();
        }
    }

    public static function createEncounterInsertStrings() {
        ksort(self::$_rankArrayForEncounters);
        foreach ( self::$_rankArrayForEncounters as $guildId => $encounterArray ) {
            $guildDetails               = CommonDataContainer::$guildArray[$guildId];
            $encounterInsertStringArray = array();

            foreach ( $encounterArray as $encounter => $rankValue ) {
                $encounter        = explode('_', $encounter);
                $rankValue        = explode('||', $rankValue);
                $encounterId      = $encounter[0];
                $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

                if ( !isset($encounterInsertStringArray[$encounterId]) ) {
                    $encounterInsertStringArray[$encounterId] = $encounterId . '<>' . implode('||', $rankValue);
                } else {
                    $encounterInsertStringArray[$encounterId] .= '++' . implode('||', $rankValue);
                }
            }

            $dbInsertString = '';

            foreach ($encounterInsertStringArray as $encounterId => $insertString ) {
                if ( empty($dbInsertString) ) {
                    $dbInsertString = $insertString;
                } else {
                    $dbInsertString .= '~~' .  $insertString;
                }
            }

            $query = self::$_dbh->query(sprintf(
                "UPDATE guild_table
                    SET rank_encounter = '%s'
                  WHERE guild_id = '%s'",
                $dbInsertString,
                $guildId
                ));
            $query->execute();
        }
    }

    public static function createDungeonRanksForGuilds() {
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

                    $dungeonTrend = self::getTrends($guildDetails, $dungeonRank, $rankSystem, $dungeonId);

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

    public static function getTrends($guildDetails, $rankDetails, $rankSystem, $dungeonId) {
        $guildDetails->generateRankDetails('dungeons', $dungeonId);
        $server       = $guildDetails->_server;
        $region       = $guildDetails->_region;
        $country      = $guildDetails->_country;
        $identifier   = $dungeonId . '_' . $rankSystem;
        $trendDetails = array();

        $newWorldRank   = $rankDetails['world'];
        $newServerRank  = $rankDetails['server'][$server];
        $newRegionRank  = $rankDetails['region'][$region];
        $newCountryRank = $rankDetails['country'][$country];

        if ( isset($guildDetails->_rankDetails->_rankDungeons->$identifier) ) {
            $rankValues = $guildDetails->_rankDetails->_rankDungeons->$identifier;

            $currentWorldRank   = $rankValues->_rank->_world;
            $currentServerRank  = $rankValues->_rank->_server;
            $currentRegionRank  = $rankValues->_rank->_region;
            $currentCountryRank = $rankValues->_rank->_country;

            $currentWorldTrend   = $rankValues->_trend->_world;
            $currentServerTrend  = $rankValues->_trend->_server;
            $currentRegionTrend  = $rankValues->_trend->_region;
            $currentCountryTrend = $rankValues->_trend->_country;

            // Rank went down Current Rank 1 New Rank 5, Trend = -4
            if ( $newWorldRank < $currentWorldRank ) { $trendDetails['trend']['world'] = $newWorldRank - $currentWorldRank; }
            if ( $newServerRank < $currentServerRank ) { $trendDetails['trend']['server'][$server] = $newServerRank - $currentServerRank; }
            if ( $newRegionRank < $currentRegionRank ) { $trendDetails['trend']['region'][$region] = $newRegionRank - $currentRegionRank; }
            if ( $newCountryRank < $currentCountryRank ) { $trendDetails['trend']['country'][$country] = $newCountryRank - $currentCountryRank; }

            // Rank went up Current Rank 5 New Rank 1, Trend = +4
            if ( $newWorldRank > $currentWorldRank ) { $trendDetails['trend']['world'] = $currentWorldRank- $newWorldRank; }
            if ( $newServerRank > $currentServerRank ) { $trendDetails['trend']['server'][$server] = $currentServerRank - $newServerRank; }
            if ( $newRegionRank > $currentRegionRank ) { $trendDetails['trend']['region'][$region] = $currentRegionRank - $newRegionRank; }
            if ( $newCountryRank > $currentCountryRank ) { $trendDetails['trend']['country'][$country] = $currentCountryRank - $newCountryRank; }

            // Rank did not change and you have no current trend
            if ( $newWorldRank == $currentWorldRank && $currentWorldTrend == '--' ) { $trendDetails['trend']['world'] = '--'; }
            if ( $newServerRank == $currentServerRank && $currentServerTrend == '--' ) { $trendDetails['trend']['server'][$server] = '--'; }
            if ( $newRegionRank == $currentRegionRank && $currentRegionTrend == '--' ) { $trendDetails['trend']['region'][$region] = '--'; }
            if ( $newCountryRank == $currentCountryRank && $currentCountryTrend == '--' ) { $trendDetails['trend']['country'][$country] = '--'; }

            // Rank did not change and you have a trend value keep it
            if ( $newWorldRank == $currentWorldRank && $currentWorldTrend != '--' ) { $trendDetails['trend']['world'] = $currentWorldTrend; }
            if ( $newServerRank == $currentServerRank && $currentServerTrend != '--' ) { $trendDetails['trend']['server'][$server] = $currentServerTrend; }
            if ( $newRegionRank == $currentRegionRank && $currentRegionTrend != '--' ) { $trendDetails['trend']['region'][$region] = $currentRegionTrend; }
            if ( $newCountryRank == $currentCountryRank && $currentCountryTrend != '--' ) { $trendDetails['trend']['country'][$country] = $currentCountryTrend; }

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

    public static function createEncounterRanksForGuilds() {
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

    public static function assignPoints() {
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
                    if ( $dungeonDetails->_finalEncounterId == $encounterId || !isset($dungeonFinalEncounterCheck[$dungeonId]) ) {
                        self::$_dungeonPointArray[$dungeonId]['QP'][$guildId]  = $qpValue . '_' . $pointValues[1];
                        $dungeonFinalEncounterCheck[$dungeonId] = true;
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

    public static function sortKills() {
        foreach ( self::$_encounterKillTimeArray as $encounterId => $guildArray ) {
            asort($guildArray);
            self::$_encounterKillTimeArray[$encounterId] = $guildArray;
        }
    }

    public static function getActiveGuildsInDungeon() {
        foreach ( CommonDataContainer::$encounterArray as $encounterId => $encounterDetails ) {

            $dungeon    = CommonDataContainer::$dungeonArray[$encounterDetails->_dungeonId];
            $numOfKills = $encounterDetails->_numOfEncounterKills;

            if ( !isset(self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId]) ) {
                self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId] = $numOfKills;
            } elseif ( $numOfKills >= self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId] ) {
                self::$_numOfGuildsActiveInDungeonArray[$dungeon->_dungeonId] = $numOfKills;
            }
        }
    }

    public static function putGuildKillsInArray() {
        foreach ( CommonDataContainer::$encounterArray as $encounterId => $encounter ) {
            foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {

                if ( isset($guildDetails->_encounterDetails->$encounterId) ) {
                    
                    $encounterDetails = $guildDetails->_encounterDetails->$encounterId;

                    self::$_encounterKillTimeArray[$encounterId][$guildId] = $encounterDetails->_strtotime;
                }
            }
        }
    }

    public static function generateDungeonKillDetails() {
        foreach ( CommonDataContainer::$dungeonArray as $dungeonId => $dungeonDetails ) {
            $dungeonDetails->setClears();
        }
    }

    public static function generateEncounterKillDetails() {
        foreach ( CommonDataContainer::$encounterArray as $encounterId => $encounter ) {
            if ( $encounter->_type != 0 ) { unset(CommonDataContainer::$encounterArray[$encounterId]); continue; }
            $encounter->setClears();

            // Assign Num Of Encounter Kills to Highest Kill Rate Per Dungeon
            if ( !isset(self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId]) ) {
                self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId] = $encounter->_numOfEncounterKills;
            } elseif ( $encounter->_numOfEncounterKills >= self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId] ) {
                self::$_highestNumOfKillsInDungeonArray[$encounter->_dungeonId] = $encounter->_numOfEncounterKills;
            }
        }
    }

    public static function generateGuildEncounterDetails() {
        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $guildDetails->generateEncounterDetails('');
        }
    }

    public static function getEncountersNeedingUpdate() {
        $dbh = DbFactory::getDbh();

        // Run query on Recent Raid Tables to retrieve un-updated encounters
        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE update_rank = '0'
           GROUP BY encounter_id",
             DbFactory::TABLE_RECENT_RAIDS
            ));

        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { 
            $encounterId = $row['encounter_id'];
            $encounter   = clone(CommonDataContainer::$encounterArray[$encounterId]);

            // If Encounter is not normal (0), dont update points
            if ( $encounter->_type == 0 ) {
                self::$_encountersNeedUpdateArray[$encounterId] = $encounter;
            }
        }
    }
}

UpdateAllPointRankings::init();