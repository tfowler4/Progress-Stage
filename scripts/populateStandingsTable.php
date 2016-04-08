<?php

include 'script.php';

class PopulateStandingsTable extends Script {
    protected static $_standingsArray         = array();
    protected static $_existingStandingsArray = array();

    public static function init() {
        exit; //Until removed
        Logger::log('INFO', 'Starting Populate Standings Table...', 'dev');

        self::getAllEncounterKills();
        self::createStandings();
        self::getExistingStandings();
        self::insertStandingsIntoDatabase();

        Logger::log('INFO', 'Update Populate Standings Table Completed!', 'dev');
    }

    public static function insertStandingsIntoDatabase() {
        $dbh = DbFactory::getDbh();

        // loop through all the guilds
        foreach ( self::$_standingsArray as $guildId => $dungeonArray ) {

            // loop through all the dungeons for the guild
            foreach ( $dungeonArray as $dungeonId => $dungeonDetails ) {

                // if the guild with dungeon standings exist, do an update query, else it will be an insert
                if ( isset(self::$_existingStandingsArray[$guildId][$dungeonId]) ) {
                    $details = self::$_standingsArray[$guildId][$dungeonId];

                    $query = $dbh->prepare(sprintf(
                        "UPDATE %s
                            SET complete = %d,
                                progress = '%s',
                                special_progress = '%s',
                                achievement = '%s',
                                world_first = %d,
                                region_first = %d,
                                server_first = %d,
                                country_first = %d,
                                recent_activity = '%s',
                                recent_time = %d
                          WHERE guild_id = %d
                            AND dungeon_id = %d", 
                                DbFactory::TABLE_STANDINGS,
                                $details['complete'],
                                $details['progress'],
                                $details['special_progress'],
                                $details['achievement'],
                                $details['world_first'],
                                $details['region_first'],
                                $details['server_first'],
                                $details['country_first'],
                                mysql_escape_string($details['recent_activity']),
                                $details['recent_time'],
                                $details['guild_id'],
                                $details['dungeon_id']
                    ));
                    $query->execute();
                } else {
                    $details = self::$_standingsArray[$guildId][$dungeonId];

                    $query = $dbh->prepare(sprintf(
                        "INSERT INTO %s
                                     (guild_id, dungeon_id, complete, progress, special_progress, achievement, world_first, region_first, server_first, country_first, recent_activity, recent_time)
                                     values('%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s','%s')",
                                     DbFactory::TABLE_STANDINGS,
                                     $details['guild_id'],
                                     $details['dungeon_id'],
                                     $details['complete'],
                                     $details['progress'],
                                     $details['special_progress'],
                                     $details['achievement'],
                                     $details['world_first'],
                                     $details['region_first'],
                                     $details['server_first'],
                                     $details['country_first'],
                                     mysql_escape_string($details['recent_activity']),
                                     $details['recent_time']
                    ));
                    $query->execute();
                }
            }
        }
    }

    public static function getExistingStandings() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->query(sprintf(
            "SELECT standings_id,
                    guild_id,
                    dungeon_id
               FROM %s", 
                    DbFactory::TABLE_STANDINGS
        ));

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId   = $row['guild_id'];
            $dungeonId = $row['dungeon_id'];
            
            if ( !isset(self::$_existingStandingsArray[$guildId]) ) {
                self::$_existingStandingsArray[$guildId] = array();
            }

            self::$_existingStandingsArray[$guildId][$dungeonId] = $row;
        }
    }

    public static function createStandings() {
        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            self::$_standingsArray[$guildId] = array();

            if ( !isset($guildDetails->_progression['dungeon']) ) { continue; }

            foreach ( $guildDetails->_progression['dungeon'] as $dungeonId => $dungeonEncounterArray ) {
                self::$_standingsArray[$guildId][$dungeonId] = array();
                $dungeonDetails                               = CommonDataContainer::$dungeonArray[$dungeonId];
                $detailsArray                                 = array();

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

                self::$_standingsArray[$guildId][$dungeonId] = $detailsArray;
            }
        }
    }

    public static function getAllEncounterKills() {
        DbFactory::getAllEncounterKills();
    }
}

PopulateStandingsTable::init();