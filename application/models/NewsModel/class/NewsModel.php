<?php

/**
 * index news page of website
 */
class NewsModel extends Model {
    protected $_article;
    //protected $_recentRaids       = array();
    protected $_newsArticles      = array();
    protected $_videoLinks        = array();
    protected $_guildRankings     = array();
    protected $_guildStandings    = array();
    
    const PAGE_TITLE            = GAME_NAME_1 . '\'s Raid Progression Tracker';
    const PAGE_DESCRIPTION      = GAME_NAME_1 . '\'s #1 Resource for raid progression tracking.';
    const LIMIT_NEWS            = 3;
    const LIMIT_RECENT_RAIDS    = 100;
    const LIMIT_GUILD_RANKINGS  = 10;
    const LIMIT_GUILD_STANDINGS = 10;
    const STANDINGS_DISPLAY     = 1;
    const STREAM_CHANNELS       = 20;

    const HEADER_STANDINGS = array(
            array('header' => 'Rank',     'key' => '_rank',       'class' => 'text-center'),
            array('header' => 'Guild',    'key' => '_nameLink',   'class' => ''),
            array('header' => 'Server',   'key' => '_serverLink', 'class' => 'hidden-xs hidden-sm'),
            array('header' => 'Progress', 'key' => '_progress',   'class' => 'text-center border-left')
        );

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title       = self::PAGE_TITLE;
        $this->description = self::PAGE_DESCRIPTION;

        if ( isset($params[0]) ) { $this->_article = $params[0]; }

        $this->_article = strtolower(str_replace("_"," ", $this->_article)); 
        $this->_article = strtolower(str_replace("poundsign","#", $this->_article));

        $this->_videoLinks     = $this->_getLiveVideos(self::STREAM_CHANNELS);
        $this->_newsArticles   = $this->_getArticles($this->_article, self::LIMIT_NEWS);
        $this->_guildStandings = $this->_getStandings(self::STANDINGS_DISPLAY, self::LIMIT_GUILD_STANDINGS);
        $this->_guildRankings  = $this->_getRankings(POINT_SYSTEM_DEFAULT, self::LIMIT_GUILD_RANKINGS);
        //$this->_recentRaids    = $this->_getRecentRaids(self::LIMIT_RECENT_RAIDS);
    }

    /**
     * get news articles
     * 
     * @param  string  $article [ title of news article ]
     * @param  integer $limit   [ maximum number of articles ]
     * 
     * @return array [ array of news articles ]
     */
    private function _getArticles($article, $limit) {
        $dataArray = array();
        $query;

        if ( !empty($article) ) {
            $query = $this->_getNewsArticle($article);
        } else {
            $query = $this->_getNews($limit);
        }

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $row['date_added']          = Functions::formatDate($row['date_added'], 'm-d-Y H:i');
            $article                    = new Article($row);
            $dataArray[$article->date]  = $article;
        }

        return $dataArray;
    }

    /**
     * get live streaming video channels
     * 
     * @param  integer $limit [ maximus number of channels ]
     * 
     * @return array [ array of live stream objects ]
     */
    private function _getLiveVideos($limit) {
        $dataArray = array();
        $query;

        $query  = $this->_getTwitchChannels($limit); 

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $dataArray[$row['twitch_id']] = new TwitchDetails($row);
            if ( !file_exists(ABS_FOLD_TWITCH . $row['twitch_id']) ) { unset($dataArray[$row['twitch_id']]); }
        }

        return $dataArray;
    }

    /**
     * get guild standings based upon type of content selected
     * 
     * @param  integer $content    [ identifier for type of standing tables ]
     * @param  integer $guildLimit [ maximum number of guilds ]
     * 
     * @return array [ array of guilds sorted by completion standings ]
     */
    private function _getStandings($content, $guildLimit) {
        /**
         * Standings Content
         * 0 - Latest Tier, Latest 2 Dungeon Worldwide
         * 1 - Latest Tier, Latest Dungeon, NA/EU Regions
         */

        // If no videos present, double the standings
        if ( empty($this->_videoLinks) ) { $guildLimit += $guildLimit; }

        $tierDetails = CommonDataContainer::$tierArray[LATEST_TIER];
        $params      = array();
        $returnArray = array();

        if ( $content == 0 ) {
            $params[0] = 'world';
            $params[1] = Functions::cleanLink($tierDetails->_name);

            $dungeonCount = 0;
            foreach ( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
                if ( $dungeonCount > 1 ) { break; }

                $params[2] = Functions::cleanLink($dungeonDetails->_name);

                $guildListing = new Listings('news', $params, $guildLimit);

                $returnArray[$dungeonId] = $guildListing->listArray->world['world'];
                $returnArray[$dungeonId]->tableFields = self::HEADER_STANDINGS;
                $returnArray[$dungeonId]->headerText  = $dungeonDetails->_name . ' Top ' . $guildLimit . ' World Guilds';
                $returnArray[$dungeonId]->dataDetails = $dungeonDetails;

                $returnArray[$dungeonId]->data = array_splice($returnArray[$dungeonId]->data, 0, $guildLimit);

                $dungeonCount++;
            }
        } elseif ( $content == 1 ) {
            $params[0] = 'region';
            $params[1] = Functions::cleanLink($tierDetails->_name);

            $dungeonCount = 0;
            foreach ( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
                if ( $dungeonCount > 0 ) { break; }

                $params[2] = Functions::cleanLink($dungeonDetails->_name);

                $guildListing = new Listings('news', $params, $guildLimit);

                foreach( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $abbreviation = $regionDetails->_abbreviation;

                    if ( isset($guildListing->listArray->region[$abbreviation]) ) {
                        $returnArray[$abbreviation] = $guildListing->listArray->region[$abbreviation];
                        $returnArray[$abbreviation]->tableFields = self::HEADER_STANDINGS;
                        $returnArray[$abbreviation]->headerText  = $dungeonDetails->_name . ' Top ' . $guildLimit . ' ' . $abbreviation . ' Guilds';
                        $returnArray[$abbreviation]->dataDetails = $dungeonDetails;

                        $returnArray[$abbreviation]->data = array_splice($returnArray[$abbreviation]->data, 0, $guildLimit);
                    }
                }

                $dungeonCount++;
            }
        }

        return $returnArray;
    }

    /**
     * get guild rankings based upon type of content selected
     * 
     * @param  integer $pointSystem [ default point ranking system ]
     * @param  integer $limit       [ maximum number of guilds ]
     * 
     * @return array [ array of guilds sorted by points ]
     */
    private function _getRankings($pointSystem, $limit) {
        $returnArray       = array();
        $dungeonStatsArray = array();

        $tierDetails = CommonDataContainer::$tierArray[LATEST_TIER];
        foreach( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
            if ( $dungeonDetails->_type != 0 ) { continue; }

            $returnArray[$dungeonId] = array();
            $params                  = array();
            $params[0]               = 'world';

            foreach( unserialize(RANK_SYSTEMS) as $systemAbbrev => $systemName ) {
                $params[1] = $systemAbbrev;
                $params[2] = Functions::cleanLink($tierDetails->_name);
                $params[3] = Functions::cleanLink($dungeonDetails->_name);

                $guildListing = new Listings('rankings', $params);

                $returnArray[$dungeonId] = $guildListing->listArray->world['world'];
                $returnArray[$dungeonId]->tableFields = self::HEADER_STANDINGS;
                $returnArray[$dungeonId]->headerText  = $dungeonDetails->_name;
                $returnArray[$dungeonId]->dataDetails = $dungeonDetails;
            }
        }

        $newReturnArray = array();

        foreach( $returnArray as $dungeonId => $guildArray ) {
            $detailsArray = array();

            foreach( $guildArray->data as $rank => $guildDetails ) {
                foreach( unserialize(RANK_SYSTEMS) as $systemAbbrev => $systemName ) {
                    $abbrev       = strtolower($systemAbbrev);
                    $guildId      = $guildDetails->_guildId;
                    $origPoints   = $guildDetails->{'_' . $abbrev . 'Points'};
                    $points       = Functions::formatPoints($guildDetails->{'_' . $abbrev . 'Points'});
                    $trend        = $guildDetails->{'_' . $abbrev . 'Trend'};
                    $rank         = $guildDetails->{'_' . $abbrev . 'Rank'};
                    $rankId       = sprintf("%02d", $rank);
                    $image        = Functions::getTrendImage($trend);
                    $identifier   = $systemAbbrev . ' | ' . $guildId;

                    $detailsArray[$rankId][$identifier] = new NewsSideRankings($guildDetails, $points, $image, $rank);
                }
            }

            ksort($detailsArray);

            foreach( $detailsArray as $rank => $systemArray ) {
                krsort($systemArray);

                $detailsArray[$rank] = $systemArray;
            }

            $detailsArray = array_splice($detailsArray, 0, $limit);

            $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

            $newReturnArray[$dungeonId]['abbreviation'] = strtolower($dungeonDetails->_abbreviation);
            $newReturnArray[$dungeonId]['name'] = $dungeonDetails->_name;
            $newReturnArray[$dungeonId]['data'] = $detailsArray;
        }

        return $newReturnArray;
    }

    /**
     * get the most recent submitted encounters sorted by kill date
     * 
     * @param  integer $limit  [ maximum number of encounter entries ]
     * 
     * @return array [ array of encounter kill data entries ]
     */
    /*
    private function _getRecentRaids($limit) {
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
    */

    /**
     * get news article query
     * 
     * @param  integer $limit [ maximum number of news articles ]
     * 
     * @return PDObject [ pdo database object ]
     */
    private function _getNews($limit) {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT news_id,
                    title,
                    content,
                    date_added,
                    added_by,
                    published,
                    type
               FROM %s
              WHERE published = 1
           ORDER BY date_added DESC
              LIMIT %s", 
                    DbFactory::TABLE_NEWS,
                    $limit));
        $query->execute();

        return $query;
    }

    /**
     * get specific news article query
     * 
     * @param  string $articleTitle [ title of news article ]
     * 
     * @return PDObject [ pdo database object ]
     */
    private function _getNewsArticle($articleTitle) {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT news_id,
                    title,
                    content,
                    date_added,
                    added_by,
                    published,
                    type
               FROM %s
              WHERE published = 1
                AND title LIKE LOWER('%s')
              LIMIT 1", 
                    DbFactory::TABLE_NEWS, 
                    $articleTitle
                ));
        $query->execute();

        return $query;
    }

    /**
     * get twitch channels query
     * 
     * @param  integer $limit [ maximum number of channels ]
     * 
     * @return PDObject [ pdo database object ]
     */
    private function _getTwitchChannels($limit) {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT twitch_num,
                    twitch_id,
                    twitch_url,
                    guild_id,
                    active,
                    viewers
               FROM %s
              WHERE active = 1
              ORDER BY viewers ASC
              LIMIT %s", 
                    DbFactory::TABLE_TWITCH,
                    $limit
                ));
        $query->execute();

        return $query;
    }
}