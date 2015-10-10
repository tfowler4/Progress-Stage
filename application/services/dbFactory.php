<?php

/**
 * database factory for common data objects 
 */
class DbFactory {
    private static $_dbh;

    const TABLE_DUNGEONS     = 'dungeon_table';
    const TABLE_ENCOUNTERS   = 'encounterlist_table';
    const TABLE_FACTIONS     = 'faction_table';
    const TABLE_TIERS        = 'tier_table';
    const TABLE_SERVERS      = 'server_table';
    const TABLE_REGIONS      = 'region_table';
    const TABLE_USERS        = 'users_table';
    const TABLE_GUILDS       = 'guild_table';
    const TABLE_NEWS         = 'news_table';
    const TABLE_COUNTRIES    = 'country_table';
    const TABLE_RECENT_RAIDS = 'recent_raid_table';
    const TABLE_DOCUMENTS    = 'document_table';
    const TABLE_SYSTEMS      = 'rank_system_table';
    const TABLE_LOGGING      = 'log_table';
    const TABLE_TWITCH       = 'twitch_table';
    const TABLE_VIDEOS       = 'video_table';
    const TABLE_KILLS        = 'encounterkills_table';

    public static function init() {
        self::$_dbh = self::getDbh();
        //self::_getTwitchChannels();
        self::_getCountries();
        self::_getServers();
        self::_getRegions();
        self::_getFactions();
        self::_getEncounters();
        self::_getDungeons();
        self::_getTiers();
        self::_getRaidSizes();
        self::_getTierRaidSizes();
        self::_getRankSystems();
        self::_getGuilds();
    }

    /**
     * get a list of all servers from database
     * 
     * @return void
     */
    private static function _getServers() {
        $query = self::$_dbh->query(sprintf(
            "SELECT server_id,
                    name,
                    country,
                    region,
                    type,
                    type2
               FROM %s
           ORDER BY region ASC, name ASC",
            self::TABLE_SERVERS
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { $row['name'] = utf8_encode($row['name']); CommonDataContainer::$serverArray[$row['name']] = new Server($row); }
    }

    /**
     * get a list of all regions from database
     * 
     * @return void
     */
    private static function _getRegions() {
        $query = self::$_dbh->query(sprintf(
            "SELECT region_id,
                    abbreviation,
                    full,
                    style,
                    num_of_servers
               FROM %s
           ORDER BY abbreviation DESC",
             self::TABLE_REGIONS
             ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$regionArray[$row['abbreviation']] = new Region($row); }
    }

    /**
     * get a list of all tiers from database
     * 
     * @return void
     */
    private static function _getTiers() {
        $query = self::$_dbh->query(sprintf(
            "SELECT tier_id,
                    tier,
                    alt_tier,
                    date_start,
                    date_end,
                    title,
                    alt_title,
                    encounters,
                    special_encounters,
                    dungeons
               FROM %s
           ORDER BY tier DESC",
            self::TABLE_TIERS
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$tierArray[$row['tier']] = new Tier($row); }
    }

    /**
     * get a list of all raid sizes from database
     * 
     * @return void
     */
    private static function _getRaidSizes() {
        foreach ( unserialize (RAID_SIZES) as $raidSize ) {
            CommonDataContainer::$raidSizeArray[$raidSize] = new RaidSize($raidSize);
        }
    }

    /**
     * get a list of raid sizes in each tier
     * 
     * @return void
     */
    private static function _getTierRaidSizes() {
        foreach ( CommonDataContainer::$tierArray as $tierId => $tierDetails ) {
            foreach ( CommonDataContainer::$raidSizeArray as $raidSize => $raidSizeDetails ) {
                $tierRaidSize = $tierDetails->_tier . '_' . $raidSizeDetails->_raidSize;

                CommonDataContainer::$tierRaidSizeArray[$tierRaidSize] = new TierRaidSize($tierDetails, $raidSize, $tierRaidSize);
            }
        }
    }

    /**
     * get a list of all dungeonsd from database
     * 
     * @return void
     */
    private static function _getDungeons() {
        $query = self::$_dbh->query(sprintf(
            "SELECT dungeon_id,
                    name,
                    abbreviation,
                    tier,
                    players,
                    mobs,
                    special_encounters,
                    final_encounter,
                    date_launch,
                    dungeon_type,
                    eu_diff
               FROM %s
           ORDER BY players DESC, dungeon_id DESC",
            self::TABLE_DUNGEONS
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$dungeonArray[$row['dungeon_id']] = new Dungeon($row); }
    }

    /**
     * get a list of all servers from database
     * 
     * @return void
     */
    private static function _getEncounters() {
        $query = self::$_dbh->query(sprintf(
            "SELECT encounter_id,
                    name,
                    dungeon,
                    dungeon_id,
                    players,
                    tier,
                    mob_type,
                    encounter_name,
                    encounter_short_name,
                    date_launch,
                    mob_order,
                    req_encounter
               FROM %s
           ORDER BY tier DESC, dungeon DESC, mob_order ASC",
            self::TABLE_ENCOUNTERS
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$encounterArray[$row['encounter_id']] = new Encounter($row); }
    }

    /**
     * get a list of all countries from database
     * 
     * @return void
     */
    private static function _getCountries() {
        $query = self::$_dbh->query(sprintf(
            "SELECT country_id,
                    name,
                    region
               FROM %s
           ORDER BY name ASC",
            self::TABLE_COUNTRIES
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$countryArray[$row['name']] = new Country($row); }
    }

    /**
     * get a list of all factions from database
     * 
     * @return void
     */
    private static function _getFactions() {
        $query = self::$_dbh->query(sprintf(
            "SELECT faction_id, 
                    name
               FROM %s
           ORDER BY faction_id DESC",
            self::TABLE_FACTIONS
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$factionArray[$row['name']] = new Faction($row); }
    }

    /**
     * get a list of all guilds from database
     * 
     * @return void
     */
    private static function _getGuilds() {
        $query = self::$_dbh->query(sprintf(
            "SELECT guild_id,
                    name,
                    date_created,
                    leader,
                    website,
                    guild_type,
                    schedule,
                    facebook,
                    twitter,
                    faction,
                    region,
                    country,
                    server,
                    active,
                    type,
                    creator_id,
                    parent,
                    child,
                    rank_tier,
                    rank_size,
                    rank_dungeon,
                    rank_encounter,
                    rank_tier_size,
                    rank_overall
               FROM %s
           ORDER BY name ASC",
            self::TABLE_GUILDS
            ));
        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) { CommonDataContainer::$guildArray[$row['guild_id']] = new GuildDetails($row); }
    }

    /**
     * get a list of all rank systems from database
     * 
     * @return void
     */
    private static function _getRankSystems() {
        $query = self::$_dbh->query(sprintf(
            "SELECT system_id,
                    identifier,
                    name,
                    abbreviation,
                    base_value,
                    final_value
               FROM %s
           ORDER BY system_id ASC",
            self::TABLE_SYSTEMS
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$rankSystemArray[$row['abbreviation']] = new RankSystem($row); }
    }

    /**
     * get a list of all twitch channels from database
     * 
     * @return void
     */
    private static function _getTwitchChannels() {
        $query = self::$_dbh->query(sprintf(
            "SELECT twitch_num,
                    twitch_id,
                    twitch_url,
                    guild_id,
                    active,
                    viewers
               FROM %s",
            self::TABLE_TWITCH
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$twitchArray[$row['twitch_num']] = $row; }
    }

    /**
     * get a list of all encounter kills from database
     * 
     * @return void
     */
    public static function getAllEncounterKills() {
        $query = self::$_dbh->query(sprintf(
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
               FROM %s",
            self::TABLE_KILLS
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
            }
        }
    }

    /**
     * get a list of specific encounter kills from database depending on parameters
     *
     * @param  string $dataType [ specify which ranking details to generate ex. encounters ]
     * @param  string $dataId   [ specify the id for a specific dungeon/encounter ]
     * 
     * @return void
     */
    public static function getEncounterKills($dataType, $dataId) {
        $sqlString = '';

        switch ($dataType) {
            case 'dungeon':
                $sqlString = 'dungeon_id ="' . $dataId . '"';
                break;
            case 'encounter':
                $sqlString = 'encounter_id ="' . $dataId . '"';
                break;
        }

        $query = self::$_dbh->query(sprintf(
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
              WHERE %s",
            self::TABLE_KILLS,
            $sqlString
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId          = $row['guild_id'];
            $encounterId      = $row['encounter_id'];
            $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonId        = $encounterDetails->_dungeonId;
            $dungeonDetails   = CommonDataContainer::$dungeonArray[$dungeonId];
            $tierId           = $dungeonDetails->_tier;
            $tierDetails      = CommonDataContainer::$tierArray[$tierId];

            if ( isset(CommonDataContainer::$guildArray[$guildId]) ) {
                $guildDetails                             = CommonDataContainer::$guildArray[$guildId];
                $arr                                      = $guildDetails->_progression;
                $arr['dungeon'][$dungeonId][$encounterId] = $row;
                $arr['encounter'][$encounterId]           = $row;
                $guildDetails->_progression               = $arr;
            } 
        }
    }

    /**
     * get a database handler
     * 
     * @return Db [ database handler ]
     */
    public static function getDbh() {
        return Db::getDbh();
    }
}

DbFactory::init();