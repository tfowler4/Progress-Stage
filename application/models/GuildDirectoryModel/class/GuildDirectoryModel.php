<?php

/**
 * guild directory listing page for all guilds registered on the site
 */
class GuildDirectoryModel extends Model {
    protected $_newGuildsArray;
    protected $_guildListing;
    protected $_detailsPane;

    const PAGE_TITLE = 'Guild Directory';
    const TABLE_HEADER = array(
            'Guild'           => '_nameLink',
            'Server'          => '_serverLink',
            'Raid Schedule'   => '_schedule',
            'WF'              => '_worldFirst',
            'RF'              => '_regionFirst',
            'SF'              => '_serverFirst',
            'Recent Activity' => '_recentActivity',
            'Website'         => '_websiteLink',
        );

    const PANE_DATA = array(
            'Total Number of Guilds'         => 'numOfGuilds',
            'Active North American Guilds'   => 'numOfActiveNAGuilds',
            'Inactive North American Guilds' => 'numOfInactiveNAGuilds',
            'Active European Guilds'         => 'numOfActiveEUGuilds',
            'Inactive European Guilds'       => 'numOfInactiveEUGuilds'
        );

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_getRecentActivityForAllGuilds();
        $this->_getFirstsForAllGuilds();

        $this->_guildListing   = $this->_getListing();
        $this->_newGuildsArray = $this->_getNewestGuilds();
        $this->_detailsPane    = $this->_getGuildData();
    }

    /**
     * run query to get last completed encounter by guilds
     * 
     * @return void
     */
    private function _getRecentActivityForAllGuilds() {
        $dbh   = DbFactory::getDbh();
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
               FROM ( SELECT kill_id,
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
                    ORDER BY datetime DESC ) as tmp_table
           GROUP BY guild_id",
            DBFactory::TABLE_KILLS
            ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId      = $row['guild_id'];
            $encounterId  = $row['encounter_id'];

            if ( isset(CommonDataContainer::$guildArray[$guildId]) ) {
                $guildDetails = CommonDataContainer::$guildArray[$guildId];

                $encounterDetails    = CommonDataContainer::$encounterArray[$encounterId];
                $dungeonId           = $encounterDetails->_dungeonId;
                $dungeonDetails      = CommonDataContainer::$dungeonArray[$dungeonId];
                $tierId              = $dungeonDetails->_tier;

                $encounter = new EncounterDetails($row, $guildDetails, $dungeonDetails);
                $guildDetails->_recentActivity = $encounter->_encounterName . ' @ ' . $encounter->_datetime;
            }
        }
    }

    /**
     * run query to get all server/region/world/country firsts for guilds
     * 
     * @return void
     */
    private function _getFirstsForAllGuilds() {
        $dbh       = DbFactory::getDbh();
        $query = $dbh->query(sprintf(
            "SELECT guild_id, 
               SUM(IF(server_rank = 1, 1,0)) AS server_firsts,
               SUM(IF(region_rank = 1, 1,0)) AS region_firsts,
               SUM(IF(world_rank = 1, 1,0)) AS world_firsts,
               SUM(IF(country_rank = 1, 1,0)) AS country_firsts
               FROM %s
               GROUP BY guild_id",
            DBFactory::TABLE_KILLS
            ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId = $row['guild_id'];

            if ( isset(CommonDataContainer::$guildArray[$guildId]) ) {
                $guildDetails = CommonDataContainer::$guildArray[$guildId];

                $guildDetails->_worldFirst   = $row['world_firsts'];
                $guildDetails->_regionFirst  = $row['region_firsts'];
                $guildDetails->_serverFirst  = $row['world_firsts'];
                $guildDetails->_countryFirst = $row['country_firsts'];
            }
        }
    }

    /**
     * get a listing of guilds in a sorted manner
     * 
     * @return array [ array sorted by region then active status ]
     */
    private function _getListing() {
        $sortArray = array();

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $guildDetails->generateEncounterDetails('');
            $region = $guildDetails->_region;
            $active = $guildDetails->_activeStatus;

            $sortArray[$active][$region][$guildId] = $guildDetails->_name;
        }

        ksort($sortArray); // Sort Regions

        foreach ( $sortArray as $active => $region ) {
            krsort($sortArray[$active]); // Active -> Inactive

            foreach ( $sortArray[$active] as $region => $guildArray ) {
                asort($sortArray[$active][$region]); // A - Z
            }
        }

        foreach ( $sortArray as $active => $region ) {
            foreach ( $sortArray[$active] as $region => $guildArray ) {
                foreach ( $sortArray[$active][$region] as $guildId => $name ) {
                    $sortArray[$active][$region][$guildId] = CommonDataContainer::$guildArray[$guildId];
                }
            }
        }

        return $sortArray;
    }

    /**
     * get the newest guilds registered
     * 
     * @return array [ array of guildd ]
     */
    private function _getNewestGuilds() {
        $sortArray    = array();
        $returnArray  = array();

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $sortArray[$guildId] = $guildDetails->_dateCreated;
        }

        arsort($sortArray);

        $limit = 3; 
        $count = 0;

        foreach ( $sortArray as $guildId => $dateCreated ) {
            if ( $count == $limit ) { break; }

            $returnArray[$guildId] = CommonDataContainer::$guildArray[$guildId];
            $count++;
        }

        return $returnArray;
    }

    /**
     * get site guild data for number of active/inactive guilds
     * 
     * @return object [ site properties ]
     */
    private function _getGuildData() {
        $returnObj = array();

        $returnObj['numOfGuilds'] = count(CommonDataContainer::$guildArray);

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( $guildDetails->_region == 'NA' ) { 
                if ( !isset($returnObj['numOfActiveNAGuilds']) ) { 
                    $returnObj['numOfActiveNAGuilds'] = 0; 
                }

                if ( !isset($returnObj['numOfInactiveNAGuilds']) ) { 
                    $returnObj['numOfInactiveNAGuilds'] = 0; 
                }

                if ( $guildDetails->_activeStatus == 'Inactive' ) {
                    $returnObj['numOfInactiveNAGuilds']++; 
                } else {
                    $returnObj['numOfActiveNAGuilds']++; 
                }
            }

            if ( $guildDetails->_region == 'EU' ) { 
                if ( !isset($returnObj['numOfActiveEUGuilds']) ) { 
                    $returnObj['numOfActiveEUGuilds'] = 0; 
                }

                if ( !isset($returnObj['numOfInactiveEUGuilds']) ) { 
                    $returnObj['numOfInactiveEUGuilds'] = 0; 
                }

                if ( $guildDetails->_activeStatus == 'Inactive' ) {
                    $returnObj['numOfInactiveEUGuilds']++; 
                } else {
                    $returnObj['numOfActiveEUGuilds']++; 
                }
            }
        }

        return (object) $returnObj;
    }
}