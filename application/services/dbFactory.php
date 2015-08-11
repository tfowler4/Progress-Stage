<?php
class DbFactory extends Db {
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

    public static function init() {
        $query = self::getTwitchChannels();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$twitchArray[$row['twitch_num']] = $row; }

        $query = self::getCountries();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$countryArray[$row['name']] = new Country($row); }

        $query = self::getServers();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { $row['name'] = utf8_encode($row['name']); CommonDataContainer::$serverArray[$row['name']] = new Server($row); }

        $query = self::getRegions();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$regionArray[$row['abbreviation']] = new Region($row); }

        $query = self::getFactions();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$factionArray[$row['name']] = new Faction($row); }
        
        $query = self::getEncounters();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$encounterArray[$row['encounter_id']] = new Encounter($row); }
        
        $query = self::getDungeons();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$dungeonArray[$row['dungeon_id']] = new Dungeon($row); }

        $query = self::getTiers();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$tierArray[$row['tier']] = new Tier($row); }

        // CREATE RAID SIZE
        foreach ( unserialize (RAID_SIZES) as $raidSize ) { CommonDataContainer::$raidSizeArray[$raidSize] = new RaidSize($raidSize); }

        // CREATE TIER SIZE
        foreach ( CommonDataContainer::$tierArray as $tierId => $tierDetails ) {
            foreach ( CommonDataContainer::$raidSizeArray as $raidSize => $raidSizeDetails ) {
                $tierSize = $tierDetails->_tier . '_' . $raidSizeDetails->_raidSize;

                CommonDataContainer::$tierSizeArray[$tierSize] = new TierSize($tierDetails, $raidSize, $tierSize);
            } 
        }

        $query = self::getRankSystems();
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) { CommonDataContainer::$rankSystemArray[$row['abbreviation']] = new RankSystem($row); }  

        $query = self::getGuilds();
        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) { CommonDataContainer::$guildArray[$row['guild_id']] = new GuildDetails($row); }
    }

    public static function getServers() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY region ASC, name ASC",
            self::TABLE_SERVERS
            ));
        return $query;
    }
    
    public static function getRegions() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY abbreviation DESC",
             self::TABLE_REGIONS
             ));
        return $query;
    }
    
    public static function getTiers() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY tier DESC",
            self::TABLE_TIERS
            ));
        return $query;
    }
    
    public static function getDungeons() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY players DESC, dungeon_id DESC",
            self::TABLE_DUNGEONS
            ));
        return $query;
    }
    
    public static function getEncounters() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY tier DESC, dungeon DESC, mob_order ASC",
            self::TABLE_ENCOUNTERS
            ));
        return $query;
    }
    
    public static function getCountries() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY name ASC",
            self::TABLE_COUNTRIES
            ));
        return $query;
    }
    
    public static function getFactions() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY faction_id DESC",
            self::TABLE_FACTIONS
            ));
        return $query;
    }
    
    public static function getGuilds() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY name ASC",
            self::TABLE_GUILDS
            ));
        return $query;
    }
    
    public static function getEmails() {
        $query = self::$_dbh->query(sprintf(
            "SELECT username, email
               FROM %s
           ORDER BY email ASC",
            self::TABLE_USERS
            ));
        return $query;
    }

    public static function getRankSystems() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s
           ORDER BY system_id ASC",
            self::TABLE_SYSTEMS
            ));
        return $query;
    }

    public static function getTwitchChannels() {
        $query = self::$_dbh->query(sprintf(
            "SELECT *
               FROM %s",
            self::TABLE_TWITCH
            ));
        return $query;
    }

    public static function getDbh() {
        return self::$_dbh;
    }
}

DbFactory::init();