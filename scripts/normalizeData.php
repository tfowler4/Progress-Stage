<?php

include 'script.php';

class NormalizeData extends Script {
    protected static $_oldFormat;
    protected static $_newFormat;

    public static function init() {
        Logger::log('INFO', 'Starting Normalize Data process...', 'dev');

        self::$_oldFormat = array(
            '0' => 'Encounter ID',
            '1' => 'Date (yyyy-mm-dd)',
            '2' => 'Time (hh:mm)',
            '3' => 'Timezone',
            '4' => 'Screenshot',
            '5' => 'Video',
            '6' => 'Server Rank',
            '7' => 'Region Rank',
            '8' => 'World Rank'
            );

        self::$_newFormat = array(
            '0' => 'Encounter ID',
            '1' => 'Date (yyyy-mm-dd)',
            '2' => 'Time (hh:mm)',
            '3' => 'Timezone',
            '4' => 'Video',
            '5' => 'Server Rank',
            '6' => 'Region Rank',
            '7' => 'World Rank',
            '8' => 'Country Rank',
            '9' => 'Server'
            );

            /*
                ***Current Order***
                0 - Encounter ID
                1 - Date (yyyy-mm-dd)
                2 - Time (hh:mm)
                3 - Timezone
                4 - Screenshot
                5 - Video
                6 - Server Rank
                7 - Region Rank
                8 - World Rank

                198||2015-05-13||19:32||EST||636-198||http://www.google.com||10||41||81
            */

           /*
                ***New Order***
                0 - Encounter ID
                1 - Date (yyyy-mm-dd)
                2 - Time (hh:mm)
                3 - Timezone
                4 - Video
                5 - Server Rank
                6 - Region Rank
                7 - World Rank
                8 - Country Rank
                9 - Server

                198||2015-05-13||19:32||EST||http://www.google.com||10||41||81||22
            */

        self::convertProgressionStringToNewFormat();
        self::insertProgressionStringIntoDatabase();

        Logger::log('INFO', 'Normalize Data Completed!', 'dev');
    }

    public static function insertProgressionStringIntoDatabase() {
        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            // Update Guild Progression String
            $query = self::$_dbh->query(sprintf(
                "UPDATE guild_table
                    SET progression = '%s'
                  WHERE guild_id = '%s'",
                $guildDetails->_progression,
                $guildId
                ));
            $query->execute();
        }
    }

    public static function convertProgressionStringToNewFormat() {
        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            // If Guild has no Progression String (aka no encounters), skip and remove from array to speed up process
            if ( empty($guildDetails->_progression) ) {
                unset(CommonDataContainer::$guildArray[$guildId]);
                continue;
            }

           $guildDetails->generateEncounterDetails('');

           $oldProgressionString = $guildDetails->_progression;
           $progressionArray     = explode('~~', $oldProgressionString);
           $newProgressionString = '';

           foreach ( $progressionArray as $encounter ) {
                $encounterDetails = explode('||', $encounter);

                // Count the number of elements in this array, if doesn't match the expected format, then change it
                $encounterDetails = self::mapNewFormat($guildDetails, $encounterDetails);
                $encounterString  = implode('||', $encounterDetails);

                if ( empty($newProgressionString) ) {
                    $newProgressionString = $encounterString;
                } else {
                    $newProgressionString .= '~~' . $encounterString;
                }
           }

            $guildDetails->_progression = $newProgressionString;
        }
    }

    public static function mapNewFormat($guildDetails, $encounterDetails) {
        $newFormatArray = array();

        if ( count($encounterDetails) != count(self::$_newFormat) ) {
            $encounterId            = $encounterDetails[0];
            $guildEncounterDetails  = $guildDetails->_encounterDetails->$encounterId;

            $newFormatArray = array(
                '0' => $guildEncounterDetails->_encounterId,
                '1' => $guildEncounterDetails->_year . '-' . $guildEncounterDetails->_month . '-' . $guildEncounterDetails->_day,
                '2' => $guildEncounterDetails->_time,
                '3' => $guildEncounterDetails->_timezone,
                '4' => $guildEncounterDetails->_video,
                '5' => (($guildEncounterDetails->_serverRank != '--') ? $guildEncounterDetails->_serverRank : 0),
                '6' => (($guildEncounterDetails->_regionRank != '--') ? $guildEncounterDetails->_regionRank : 0),
                '7' => (($guildEncounterDetails->_worldRank != '--') ? $guildEncounterDetails->_worldRank : 0),
                '8' => (($guildEncounterDetails->_countryRank != '--') ? $guildEncounterDetails->_countryRank : 0),
                '9' => $guildEncounterDetails->_server
                );
        } else {
            $newFormatArray = $encounterDetails;
        }

        return $newFormatArray;
    }
}

NormalizeData::init();