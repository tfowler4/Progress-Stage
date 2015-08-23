<?php

include 'script.php';

class UpdateEncounterStandings extends Script {
    protected static $_encounterKillArray;
    protected static $_sortedEncounterKillArray;

    public static function init() {
        Logger::log('INFO', 'Starting Update Encounter Standings...', 'dev');

        self::getAllEncounterKills();
        self::sortKillsByTime();
        self::setNewKillRanks();
        self::insertUpdatedGuildKills();

        Logger::log('INFO', 'Update Encounter Standings Completed!', 'dev');
    }

    public static function getAllEncounterKills() {
        // Loop through all guilds
        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $guildDetails->generateEncounterDetails('');

            // Loop through all guild encounters
            foreach ( $guildDetails->_encounterDetails as $encounterId => $encounterDetails ) {
                $encounterKillTime  = $encounterDetails->_strtotime;
                $encounterSpecifics = CommonDataContainer::$encounterArray[$encounterId];
                $dungeonDetails     = CommonDataContainer::$dungeonArray[$encounterSpecifics->_dungeonId];

                // Place Kill into Kill Array
                self::$_encounterKillArray[$encounterId][$guildId] = $encounterKillTime;
            }
        }
    }

    public static function sortKillsByTime() {
        // Loop through all encounters and sort the inner guild array
        foreach ( self::$_encounterKillArray as $encounterId => $guildArray ) {
            asort($guildArray);
            self::$_sortedEncounterKillArray[$encounterId] = $guildArray;
        }
    }

    public static function setNewKillRanks() {
        // Loop through all encounters and assign the kill rank per set
        foreach ( self::$_sortedEncounterKillArray as $encounterId => $guildArray ) {

            // New Rank Array per Encounter
            $rankArray = array();

            foreach ( $guildArray as $guildId => $killTime ) {
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

    public static function insertUpdatedGuildKills() {
        // Loop through all guilds
        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $insertString = '';

            // Loop through all guild encounters
            foreach ( $guildDetails->_encounterDetails as $encounterId => $encounterDetails ) {
                // Set Insert String
                $insertString = Functions::generateDBInsertString($insertString, $encounterDetails, $guildId);
            }

            // Update Guild Progression String
            $query = self::$_dbh->query(sprintf(
                "UPDATE guild_table
                    SET progression = '%s'
                  WHERE guild_id = '%s'",
                $insertString,
                $guildId
                ));
            $query->execute();
        }
    }
}

UpdateEncounterStandings::init();