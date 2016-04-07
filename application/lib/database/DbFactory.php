<?php

/**
 * database factory for common data objects 
 */
class DbFactory {
    private static $_dbh;

    const TABLE_DUNGEONS           = 'dungeon_table';
    const TABLE_ENCOUNTERS         = 'encounterlist_table';
    const TABLE_FACTIONS           = 'faction_table';
    const TABLE_TIERS              = 'tier_table';
    const TABLE_SERVERS            = 'server_table';
    const TABLE_REGIONS            = 'region_table';
    const TABLE_USERS              = 'users_table';
    const TABLE_GUILDS             = 'guild_table';
    const TABLE_NEWS               = 'news_table';
    const TABLE_COUNTRIES          = 'country_table';
    const TABLE_RECENT_RAIDS       = 'recent_raid_table';
    const TABLE_DOCUMENTS          = 'document_table';
    const TABLE_SYSTEMS            = 'rank_system_table';
    const TABLE_LOGGING            = 'log_table';
    const TABLE_TWITCH             = 'twitch_table';
    const TABLE_VIDEOS             = 'video_table';
    const TABLE_KILLS              = 'encounterkills_table';
    const TABLE_STANDINGS          = 'standings_table';
    const TABLE_RANKINGS           = 'rankings_table';
    const TABLE_ENCOUNTER_RANKINGS = 'encounter_rankings_table';

    public static function init() {
        self::$_dbh = self::getDbh();
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
        CommonDataContainer::$recentRaidsArray = self::_getRecentRaids();
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
                    dungeons,
                    era
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
                    child
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
     * get the most recent submitted encounters sorted by kill date
     * 
     * @param  integer $limit  [ maximum number of encounter entries ]
     * 
     * @return array [ array of encounter kill data entries ]
     */
    private static function _getRecentRaids($limit = 100) {
        $dbh          = DbFactory::getDbh();
        $dataArray    = array();
        $enAlignArray = array();

        $query = $dbh->prepare(sprintf(
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
              FROM  %s
          ORDER BY  datetime DESC
             LIMIT  %s", 
                    DbFactory::TABLE_KILLS,
                    $limit
        ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId        = $row['guild_id'];
            $encounterId    = $row['encounter_id'];
            $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonId      = $row['dungeon_id'];
            $dungeonId      = $encounterDetails->_dungeonId;
            $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
            $identifier     = $guildId . '|' . $encounterId;

            if ( isset(CommonDataContainer::$guildArray[$guildId]) ) {
                $guildDetails                             = CommonDataContainer::$guildArray[$guildId];
                $arr                                      = $guildDetails->_progression;
                $arr['dungeon'][$dungeonId][$encounterId] = $row;
                $arr['encounter'][$encounterId]           = $row;
                $guildDetails->_progression               = $arr;
            } 

            if ( !isset(CommonDataContainer::$guildArray[$guildId]) ) { continue; }

            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $guildDetails->generateEncounterDetails('encounter', $encounterId);

            if ( !isset($guildDetails->_encounterDetails->$encounterId) ) { continue; }

            $dataArray[$identifier] = new RecentKillObject($guildDetails, $encounterId);

            // Apply EU Time Diff
            $strtotime = strtotime($row['datetime']);
            if ( $guildDetails->_region == 'EU' ) {
                $strtotime = strtotime("-". EU_TIME_DIFF . ' minutes', $strtotime);
            }

            if ( $guildDetails->_region == 'EU' && $dungeonDetails->_euTimeDiff > 0 ) {
                $strtotime = strtotime("-". ($dungeonDetails->_euTimeDiff) . ' minutes', $strtotime);
            }

            $euAlignArray[$identifier] = $strtotime;
        }

        arsort($euAlignArray);

        foreach( $euAlignArray as $identifier => $strtotime) {
            $euAlignArray[$identifier] = $dataArray[$identifier];
        }

        return $euAlignArray;
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

    public static function getGuildEncounterRankings($guildId, $dataType, $dataId = '') {
        $sqlString = 'guild_id ="' . $guildId . '"';

        switch ($dataType) {
            case 'dungeon':
                $sqlString = ' AND dungeon_id ="' . $dataId . '"';
                break;
        }

        $query = self::$_dbh->query(sprintf(
            "SELECT rankings_id,
                    guild_id,
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
                    apf_country_rank
               FROM %s
              WHERE %s",
            self::TABLE_ENCOUNTER_RANKINGS,
            $sqlString
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId          = $row['guild_id'];
            $encounterId      = $row['encounter_id'];

            if ( isset(CommonDataContainer::$guildArray[$guildId]) ) {
                $guildDetails                 = CommonDataContainer::$guildArray[$guildId];
                $arr                          = $guildDetails->_rankEncounter;
                $arr[$encounterId]            = $row;
                $guildDetails->_rankEncounter = $arr;
            } 
        }
    }
/*
    public static function getGuildDungeonRankings($guildId, $dataId = '') {
        $sqlString = 'guild_id ="' . $guildId . '"';

        if ( !empty() ) {
            $sqlString = ' AND dungeon_id ="' . $dataId . '"';
        }

        $query = self::$_dbh->query(sprintf(
            "SELECT rankings_id,
                    guild_id,
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
                    apf_country_rank
               FROM %s
              WHERE %s",
            self::TABLE_ENCOUNTER_RANKINGS,
            $sqlString
            ));
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId          = $row['guild_id'];
            $encounterId      = $row['encounter_id'];

            if ( isset(CommonDataContainer::$guildArray[$guildId]) ) {
                $guildDetails                 = CommonDataContainer::$guildArray[$guildId];
                $arr                          = $guildDetails->_rankEncounter;
                $arr[$encounterId]            = $row;
                $guildDetails->_rankEncounter = $arr;
            } 
        }
    }
*/
    /**
     * get a list of specific encounter kills in order of earliest to latest
     *
     * @param  string $encounterId [ id of encounter in database ]
     * 
     * @return void
     */
    public static function getStandingsForEncounter($encounterId) {
        $guildArray   = array();
        $euAlignArray = array();

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
              WHERE encounter_id = %d
            ORDER BY datetime ASC",
            self::TABLE_KILLS,
            $encounterId
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

                $guildArray[$guildId] = $guildDetails;

                // Apply EU Time Diff
                $strtotime = strtotime($row['datetime']);
                if ( $guildDetails->_region == 'EU' ) {
                    $strtotime = strtotime("-". EU_TIME_DIFF . ' minutes', $strtotime);
                }

                if ( $guildDetails->_region == 'EU' && $dungeonDetails->_euTimeDiff > 0 ) {
                    $strtotime = strtotime("-". ($dungeonDetails->_euTimeDiff) . ' minutes', $strtotime);
                }

                $euAlignArray[$guildId] = $strtotime;
            }

        }

        asort($euAlignArray);

        foreach( $euAlignArray as $guildId => $strtotime) {
            $euAlignArray[$guildId] = $guildArray[$guildId];
        }

        return $euAlignArray;
    }

    /**
     * get a list of all standing data for a specific dungeon
     *
     * @param  string $dungeonId [ id for dungeon ]
     * @param  string $limit     [ limit number of guilds to get ]
     * 
     * @return void
     */
    public static function getStandingsForDungeon($dungeonId, $acceptableGuilds) {
        $guildArray = array();

        $query = self::$_dbh->query(sprintf(
            "SELECT standings_id,
                    guild_id,
                    dungeon_id,
                    complete,
                    progress,
                    special_progress,
                    achievement,
                    world_first,
                    region_first,
                    server_first,
                    country_first,
                    recent_time,
                    recent_activity,
                    world_rank,
                    region_rank,
                    server_rank,
                    country_rank,
                    world_trend,
                    region_trend,
                    server_trend,
                    country_trend
               FROM %s
              WHERE dungeon_id = %d
           ORDER BY complete DESC,
                    recent_time ASC",
            self::TABLE_STANDINGS,
            $dungeonId
        ));

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId              = $row['guild_id'];

            if ( !isset($acceptableGuilds[$guildId]) ) { continue; }

            $guildArray[$guildId] = new StandingsDataObject($row);
        }

        return $guildArray;
    }

    /**
     * get a list of all standing data for a specific dungeon
     *
     * @param  string $dungeonId [ id for dungeon ]
     * @param  string $guildId   [ id for guild ]
     * 
     * @return void
     */
    public static function getStandingsForGuildInDungeon($dungeonId, $guildId) {
        $query = self::$_dbh->query(sprintf(
            "SELECT standings_id,
                    guild_id,
                    dungeon_id,
                    complete,
                    progress,
                    special_progress,
                    achievement,
                    world_first,
                    region_first,
                    server_first,
                    country_first,
                    recent_time,
                    recent_activity,
                    world_rank,
                    region_rank,
                    server_rank,
                    country_rank,
                    world_trend,
                    region_trend,
                    server_trend,
                    country_trend
               FROM %s
              WHERE dungeon_id = %d
                AND guild_id = %d
           ORDER BY complete DESC,
                    recent_time ASC",
            self::TABLE_STANDINGS,
            $dungeonId,
            $guildId
        ));

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId = $row['guild_id'];
            return new StandingsDataObject($row);
        }

        return null;
    }

    /**
     * get a list of all standing data for a specific dungeon
     *
     * @param  string $dungeonId [ id for dungeon ]
     * @param  string $limit     [ limit number of guilds to get ]
     * 
     * @return void
     */
    public static function getRankingsForDungeon($dungeonId, $rankType, $view) {
        $guildArray = array();

        $query = self::$_dbh->query(sprintf(
                    "SELECT %s.guild_id,
                            %s.dungeon_id,
                            %s.recent_time,
                            %s.recent_activity,
                            %s.world_first,
                            %s.region_first,
                            %s.server_first,
                            %s.country_first,
                            %s.progress,
                            %s.complete,
                            %s.special_progress,
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
                            apf_country_prev_rank
                       FROM %s
            LEFT OUTER JOIN %s
                         ON %s.guild_id = %s.guild_id
                         WHERE %s.dungeon_id = %d
                           AND %s.dungeon_id = %d",
            self::TABLE_RANKINGS,
            self::TABLE_RANKINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_STANDINGS,
            self::TABLE_RANKINGS,
            self::TABLE_STANDINGS,
            self::TABLE_RANKINGS,
            self::TABLE_STANDINGS,
            self::TABLE_RANKINGS,
            $dungeonId,
            self::TABLE_STANDINGS,
            $dungeonId
        ));

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId              = $row['guild_id'];
            $guildArray[$guildId] = new RankingsDataObject($row, $rankType, $view);
        }

        return $guildArray;
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