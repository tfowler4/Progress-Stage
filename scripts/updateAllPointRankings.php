<?php

include 'script.php';

class UpdateAllPointRankings extends Script {
    protected static $_encountersNeedUpdateArray;
    protected static $_encounterKillTimeArray;
    protected static $_encounterPointArray;
    protected static $_dungeonPointArray;
    protected static $_highestNumOfKillsInDungeonArray;
    protected static $_numOfGuildsActiveInDungeonArray;
    protected static $_rankArrayForEncounters;
    protected static $_rankArrayForDungeons;
    protected static $_existingRankingsArray = array();
    protected static $_existingEncounterRankingsArray = array();

    public static function init() {
        echo "Running Standings<br>";
        //StandingsHandler::update(1, 216, 41);
        echo "<br>Ending Run of Standings";
        //exit;
        echo "Running Rankings<br>";
        RankingsHandler::update(1, 216, 41);
        echo "<br>Ending Run of Rankings";
        exit;

        Logger::log('INFO', 'Starting Update All Point Rankings...', 'dev');

        Logger::log('INFO', 'Encounters Needing Updates', 'dev');
        self::getEncountersNeedingUpdate();

        if ( !empty(self::$_encountersNeedUpdateArray) ) {
            Logger::log('INFO', 'Updating Recent Raid Table', 'dev');
            self::getExistingRankings();

            Logger::log('INFO', 'Generating Guild Details', 'dev');
            self::generateGuildEncounterDetails();

            Logger::log('INFO', 'Generate Encounter Kill Details', 'dev');
            self::generateEncounterKillDetails();

            Logger::log('INFO', 'Generate Dungeon Kill Details', 'dev');
            self::generateDungeonKillDetails();

            Logger::log('INFO', 'Put Guild Kills Into Array', 'dev');
            self::putGuildKillsInArray();

            Logger::log('INFO', 'Get Active Guilds in Dungeon', 'dev');
            self::getActiveGuildsInDungeon();

            Logger::log('INFO', 'Sort Kills By Time Per Encounter', 'dev');
            self::sortKills();

            Logger::log('INFO', 'Assign Points To Encounters', 'dev');
            self::assignPoints();

            Logger::log('INFO', 'Create Encounter Ranks For Guilds', 'dev');
            self::createEncounterRanksForGuilds();

            Logger::log('INFO', 'Create Dungeon Ranks And Trends For Guilds', 'dev');
            self::createDungeonRanksForGuilds();

            Logger::log('INFO', 'Create Encounter Database Insert Strings', 'dev');
            self::createEncounterInsertStrings();

            Logger::log('INFO', 'Create Dungeon Database Insert Strings', 'dev');
            self::createDungeonInsertStrings();

            Logger::log('INFO', 'Updating Recent Raid Table', 'dev');
            self::updateRecentRaidTable();
        }

        Logger::log('INFO', 'Update All Point Rankings Completed!', 'dev');
    }

    public static function getExistingRankings() {
        $dbh = DbFactory::getDbh();

        // getting all dungeon rankings
        $query = $dbh->query(sprintf(
            "SELECT rankings_id,
                    guild_id,
                    dungeon_id
               FROM %s", 
                    DbFactory::TABLE_RANKINGS
        ));

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId   = $row['guild_id'];
            $dungeonId = $row['dungeon_id'];
            
            if ( !isset(self::$_existingRankingsArray[$guildId]) ) {
                self::$_existingRankingsArray[$guildId] = array();
            }

            self::$_existingRankingsArray[$guildId][$dungeonId] = $row;
        }

        // getting all encounter rankings
        $query = $dbh->query(sprintf(
            "SELECT rankings_id,
                    guild_id,
                    encounter_id
               FROM %s", 
                    DbFactory::TABLE_ENCOUNTER_RANKINGS
        ));

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId     = $row['guild_id'];
            $encounterId = $row['encounter_id'];
            
            if ( !isset(self::$_existingEncounterRankingsArray[$guildId]) ) {
                self::$_existingEncounterRankingsArray[$guildId] = array();
            }

            self::$_existingEncounterRankingsArray[$guildId][$encounterId] = $row;
        }
    }

    public static function updateRecentRaidTable() {
        $query = self::$_dbh->prepare(sprintf(
            "UPDATE %s
                SET update_rank = 1",
            DbFactory::TABLE_RECENT_RAIDS
            ));
        $query->execute();
    }

    public static function createDungeonInsertStrings() {
        ksort(self::$_rankArrayForDungeons);
        foreach ( self::$_rankArrayForDungeons as $guildId => $dungeonArray ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $detailsArray = array();

            foreach ( $dungeonArray as $dungeon => $rankValue ) {
                $dungeon        = explode('_', $dungeon);
                $rankValue      = explode('||', $rankValue);
                $dungeonId      = $dungeon[0];
                $system         = strtolower($dungeon[1]);
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

            // new rankings table code
            foreach ( $detailsArray as $dungeonId => $details ) {
                if ( isset(self::$_existingRankingsArray[$guildId][$dungeonId]) ) {
                    $query = self::$_dbh->prepare(sprintf(
                        "UPDATE %s
                            SET progress ='%s',
                                special_progress = '%s',
                                qp_points = '%s',
                                qp_world_rank = '%s',
                                qp_region_rank = '%s',
                                qp_server_rank = '%s',
                                qp_country_rank = '%s',
                                qp_world_trend = '%s',
                                qp_region_trend = '%s',
                                qp_server_trend = '%s',
                                qp_country_trend = '%s',
                                qp_world_prev_rank = '%s',
                                qp_region_prev_rank = '%s',
                                qp_server_prev_rank = '%s',
                                qp_country_prev_rank = '%s',
                                ap_points = '%s',
                                ap_world_rank = '%s',
                                ap_region_rank = '%s',
                                ap_server_rank = '%s',
                                ap_country_rank = '%s',
                                ap_world_trend = '%s',
                                ap_region_trend = '%s',
                                ap_server_trend = '%s',
                                ap_country_trend = '%s',
                                ap_world_prev_rank = '%s',
                                ap_region_prev_rank = '%s',
                                ap_server_prev_rank = '%s',
                                ap_country_prev_rank = '%s',
                                apf_points = '%s',
                                apf_world_rank = '%s',
                                apf_region_rank = '%s',
                                apf_server_rank = '%s',
                                apf_country_rank = '%s',
                                apf_world_trend = '%s',
                                apf_region_trend = '%s',
                                apf_server_trend = '%s',
                                apf_country_trend = '%s',
                                apf_world_prev_rank = '%s',
                                apf_region_prev_rank = '%s',
                                apf_server_prev_rank = '%s',
                                apf_country_prev_rank = '%s'
                          WHERE guild_id = %d
                            AND dungeon_id = %d",
                        DbFactory::TABLE_RANKINGS,
                        $details['progress'],
                        $details['special_progress'],
                        $details['qp_points'],
                        $details['qp_world_rank'],
                        $details['qp_region_rank'],
                        $details['qp_server_rank'],
                        $details['qp_country_rank'],
                        $details['qp_world_trend'],
                        $details['qp_region_trend'],
                        $details['qp_server_trend'],
                        $details['qp_country_trend'],
                        $details['qp_world_prev_rank'],
                        $details['qp_region_prev_rank'],
                        $details['qp_server_prev_rank'],
                        $details['qp_country_prev_rank'],
                        $details['ap_points'],
                        $details['ap_world_rank'],
                        $details['ap_region_rank'],
                        $details['ap_server_rank'],
                        $details['ap_country_rank'],
                        $details['ap_world_trend'],
                        $details['ap_region_trend'],
                        $details['ap_server_trend'],
                        $details['ap_country_trend'],
                        $details['ap_world_prev_rank'],
                        $details['ap_region_prev_rank'],
                        $details['ap_server_prev_rank'],
                        $details['ap_country_prev_rank'],
                        $details['apf_points'],
                        $details['apf_world_rank'],
                        $details['apf_region_rank'],
                        $details['apf_server_rank'],
                        $details['apf_country_rank'],
                        $details['apf_world_trend'],
                        $details['apf_region_trend'],
                        $details['apf_server_trend'],
                        $details['apf_country_trend'],
                        $details['apf_world_prev_rank'],
                        $details['apf_region_prev_rank'],
                        $details['apf_server_prev_rank'],
                        $details['apf_country_prev_rank'],
                        $guildId,
                        $dungeonId
                    ));
                    $query->execute();
                } else {
                    $query = self::$_dbh->prepare(sprintf(
                        "INSERT INTO %s
                               (progress,
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
                                apf_country_prev_rank,
                                guild_id,
                                dungeon_id)
                          values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s', '%s')",
                        DbFactory::TABLE_RANKINGS,
                        $details['progress'],
                        $details['special_progress'],
                        $details['qp_points'],
                        $details['qp_world_rank'],
                        $details['qp_region_rank'],
                        $details['qp_server_rank'],
                        $details['qp_country_rank'],
                        $details['qp_world_trend'],
                        $details['qp_region_trend'],
                        $details['qp_server_trend'],
                        $details['qp_country_trend'],
                        $details['qp_world_prev_rank'],
                        $details['qp_region_prev_rank'],
                        $details['qp_server_prev_rank'],
                        $details['qp_country_prev_rank'],
                        $details['ap_points'],
                        $details['ap_world_rank'],
                        $details['ap_region_rank'],
                        $details['ap_server_rank'],
                        $details['ap_country_rank'],
                        $details['ap_world_trend'],
                        $details['ap_region_trend'],
                        $details['ap_server_trend'],
                        $details['ap_country_trend'],
                        $details['ap_world_prev_rank'],
                        $details['ap_region_prev_rank'],
                        $details['ap_server_prev_rank'],
                        $details['ap_country_prev_rank'],
                        $details['apf_points'],
                        $details['apf_world_rank'],
                        $details['apf_region_rank'],
                        $details['apf_server_rank'],
                        $details['apf_country_rank'],
                        $details['apf_world_trend'],
                        $details['apf_region_trend'],
                        $details['apf_server_trend'],
                        $details['apf_country_trend'],
                        $details['apf_world_prev_rank'],
                        $details['apf_region_prev_rank'],
                        $details['apf_server_prev_rank'],
                        $details['apf_country_prev_rank'],
                        $guildId,
                        $dungeonId
                    ));
                    $query->execute();
                }
            }
        }
    }

    public static function createEncounterInsertStrings() {
        ksort(self::$_rankArrayForEncounters);
        foreach ( self::$_rankArrayForEncounters as $guildId => $encounterArray ) {
            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $detailsArray = array();

            foreach ( $encounterArray as $encounter => $rankValue ) {
                $encounter        = explode('_', $encounter);
                $rankValue        = explode('||', $rankValue);
                $encounterId      = $encounter[0];
                $system           = strtolower($encounter[1]);
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

            // new rankings table code
            foreach ( $detailsArray as $encounterId => $details ) {
                if ( isset(self::$_existingEncounterRankingsArray[$guildId][$encounterId]) ) {
                    $query = self::$_dbh->prepare(sprintf(
                        "UPDATE %s
                            SET qp_points = '%s',
                                qp_world_rank = '%s',
                                qp_region_rank = '%s',
                                qp_server_rank = '%s',
                                qp_country_rank = '%s',
                                ap_points = '%s',
                                ap_world_rank = '%s',
                                ap_region_rank = '%s',
                                ap_server_rank = '%s',
                                ap_country_rank = '%s',
                                apf_points = '%s',
                                apf_world_rank = '%s',
                                apf_region_rank = '%s',
                                apf_server_rank = '%s',
                                apf_country_rank = '%s'
                          WHERE guild_id = %d
                            AND encounter_id = %d",
                        DbFactory::TABLE_ENCOUNTER_RANKINGS,
                        $details['qp_points'],
                        $details['qp_world_rank'],
                        $details['qp_region_rank'],
                        $details['qp_server_rank'],
                        $details['qp_country_rank'],
                        $details['ap_points'],
                        $details['ap_world_rank'],
                        $details['ap_region_rank'],
                        $details['ap_server_rank'],
                        $details['ap_country_rank'],
                        $details['apf_points'],
                        $details['apf_world_rank'],
                        $details['apf_region_rank'],
                        $details['apf_server_rank'],
                        $details['apf_country_rank'],
                        $guildId,
                        $encounterId
                    ));
                    $query->execute();
                } else {
                    $query = self::$_dbh->prepare(sprintf(
                        "INSERT INTO %s
                               (qp_points,
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
                                apf_country_rank,
                                guild_id,
                                dungeon_id,
                                encounter_id)
                          values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
                        DbFactory::TABLE_ENCOUNTER_RANKINGS,
                        $details['qp_points'],
                        $details['qp_world_rank'],
                        $details['qp_region_rank'],
                        $details['qp_server_rank'],
                        $details['qp_country_rank'],
                        $details['ap_points'],
                        $details['ap_world_rank'],
                        $details['ap_region_rank'],
                        $details['ap_server_rank'],
                        $details['ap_country_rank'],
                        $details['apf_points'],
                        $details['apf_world_rank'],
                        $details['apf_region_rank'],
                        $details['apf_server_rank'],
                        $details['apf_country_rank'],
                        $details['guild_id'],
                        $details['dungeon_id'],
                        $details['encounter_id']
                    ));
                    $query->execute();
                }
            }
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
        $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
        $server         = $guildDetails->_server;
        $region         = $guildDetails->_region;
        $country        = $guildDetails->_country;
        $identifier     = '_' . strtolower($rankSystem);
        $trendDetails   = array();

        $newWorldRank   = $rankDetails['world'];
        $newServerRank  = $rankDetails['server'][$server];
        $newRegionRank  = $rankDetails['region'][$region];
        $newCountryRank = $rankDetails['country'][$country];

        if ( isset($guildDetails->_dungeonDetails->$dungeonId->$identifier) ) {
            $rankValues = $guildDetails->_dungeonDetails->$dungeonId->$identifier;

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
        DbFactory::getAllEncounterKills();

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

            if ( !isset(CommonDataContainer::$encounterArray[$encounterId]) ) { continue; }

            $encounter = clone(CommonDataContainer::$encounterArray[$encounterId]);

            // If Encounter is not normal (0), dont update points
            if ( $encounter->_type == 0 ) {
                self::$_encountersNeedUpdateArray[$encounterId] = $encounter;
            }
        }
    }
}

UpdateAllPointRankings::init();