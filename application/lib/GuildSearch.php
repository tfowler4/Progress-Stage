<?php

/**
 * class to handle site guild searching function
 */
class GuildSearch {
    protected static $_queryString;

    public static $searchResults = array();

    /**
     * runs a query to search database for guild names similar 
     * to what is in the Post queryTerm field
     * 
     * @return array [ guild search results ]
     */
    public static function getSearchResults() {
        self::$_queryString = Post::get('queryTerm');

        if ( empty(self::$_queryString) ) { return ''; }

        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT guild_id,
                    name,
                    date_created,
                    leader,
                    website,
                    guild_type,
                    schedule,
                    facebook,
                    twitter,
                    google,
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
              WHERE name LIKE '%s' 
           ORDER BY name DESC", 
                    DbFactory::TABLE_GUILDS,
                    '%' . self::$_queryString . '%'
                    ));
        $query->execute();
        
        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $guildId = $row['guild_id'];
            self::$searchResults[$guildId] = CommonDataContainer::$guildArray[$guildId];
        }

        return self::$searchResults;
    }
}