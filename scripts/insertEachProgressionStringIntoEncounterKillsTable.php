<?php

include 'script.php';

class InsertEachProgressionStringIntoEncounterKillsTable {

    public static function init() {
        $dbh = DbFactory::getDbh();

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails) {
            $guildDetails->generateEncounterDetails('');

            foreach ( (array)$guildDetails->_encounterDetails as $encounterId => $encounterDetails ) {

                if ( !empty($encounterDetails->_video) ) {
                    $query = $dbh->prepare(sprintf(
                        "INSERT INTO %s
                        (guild_id, 
                        encounter_id, 
                        datetime, 
                        time_zone, 
                        server,
                        videos,
                        server_rank,
                        region_rank,
                        world_rank,
                        country_rank)
                        values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                        DbFactory::TABLE_KILLS,
                        $encounterDetails->_guildId,
                        $encounterDetails->_encounterId,
                        $encounterDetails->_date . ' ' . $encounterDetails->_time,
                        $encounterDetails->_timezone,
                        $encounterDetails->_server,
                        1,
                        $encounterDetails->_serverRank,
                        $encounterDetails->_regionRank,
                        $encounterDetails->_worldRank,
                        $encounterDetails->_countryRank
                        ));
                    $query->execute();

                    $query = $dbh->prepare(sprintf(
                        "INSERT INTO %s
                        (guild_id, encounter_id, url, type, notes)
                        values('%s','%s','%s','%s','%s')",
                         DbFactory::TABLE_VIDEOS,
                         $encounterDetails->_guildId,
                         $encounterDetails->_encounterId,
                         $encounterDetails->_video,
                         0,
                         'General Kill Video'
                        ));
                    $query->execute();
                } else {
                    $query = $dbh->prepare(sprintf(
                        "INSERT INTO %s
                        (guild_id, 
                        encounter_id, 
                        datetime, 
                        time_zone, 
                        server,
                        server_rank,
                        region_rank,
                        world_rank,
                        country_rank)
                        values ('%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s')",
                        DbFactory::TABLE_KILLS,
                        $encounterDetails->_guildId,
                        $encounterDetails->_encounterId,
                        $encounterDetails->_date . ' ' . $encounterDetails->_time,
                        $encounterDetails->_timezone,
                        $encounterDetails->_server,
                        $encounterDetails->_serverRank,
                        $encounterDetails->_regionRank,
                        $encounterDetails->_worldRank,
                        $encounterDetails->_countryRank
                        ));
                    $query->execute();
                }
            }
        }
    }
}

InsertEachProgressionStringIntoEncounterKillsTable::init();