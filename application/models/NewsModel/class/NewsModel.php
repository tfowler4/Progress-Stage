<?php

/**
 * index news page of website
 */
class NewsModel extends Model {
    protected $_article;
    protected $_recentRaids       = array();
    protected $_newsArticles      = array();
    protected $_videoLinks        = array();
    protected $_guildRankings     = array();
    protected $_guildStandings    = array();
    protected $_dungeonGuildArray = array();
    protected $_standingsTableHeader;
    
    const PAGE_TITLE            = GAME_NAME_1 . '\'s Raid Progression Tracker';
    const PAGE_DESCRIPTION      = GAME_NAME_1 . '\'s #1 Resource for raid progression tracking.';
    const LIMIT_NEWS            = 3;
    const LIMIT_RECENT_RAIDS    = 100;
    const LIMIT_GUILD_RANKINGS  = 10;
    const LIMIT_GUILD_STANDINGS = 10;
    const STANDINGS_DISPLAY     = 1;
    const STREAM_CHANNELS       = 20;

    const HEADER_STANDINGS = array(
            'Rank'     => '_rank',
            'Guild'    => '_nameLink',
            'Server'   => '_serverLink',
            'Progress' => '_standing'
        );

    /**
     * constructor
     */
    public function __construct($module, $article) {
        parent::__construct();

        $this->title       = self::PAGE_TITLE;
        $this->description = self::PAGE_DESCRIPTION;

        if ( isset($params[0]) ) { $this->_article = $article; }

        $this->_article = strtolower(str_replace("_"," ", $this->_article)); 
        $this->_article = strtolower(str_replace("poundsign","#", $this->_article));

        $this->_videoLinks     = $this->_getLiveVideos(self::STREAM_CHANNELS);
        $this->_newsArticles   = $this->_getArticles($this->article, self::LIMIT_NEWS);
        $this->_guildStandings = $this->_getStandings(self::STANDINGS_DISPLAY, self::LIMIT_GUILD_STANDINGS);
        $this->_guildRankings  = $this->_getRankings(POINT_SYSTEM_DEFAULT, self::LIMIT_GUILD_RANKINGS);
        $this->_recentRaids    = $this->_getRecentRaids(self::LIMIT_RECENT_RAIDS);

        $this->_standingsTableHeader = self::HEADER_STANDINGS;
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
            if ( !file_exists(ABSOLUTE_PATH . '/public/images/' . strtolower(GAME_NAME_1) . '/twitch/' . $row['twitch_id']) ) { unset($dataArray[$row['twitch_id']]); }
        }

        return $dataArray;
    }

    /**
     * get sorted guild array based on number of encounters completed in dungeon
     * 
     * @param  Dungeon $dungeonDetails [ dungeon data object ]
     * 
     * @return array [ sorted guild array by amount completed ]
     */
    private function _getTemporarySortArray($dungeonDetails) {
        $sortArray                = array();
        $this->_dungeonGuildArray = array();
        $dungeonId                = $dungeonDetails->_dungeonId;

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $this->_dungeonGuildArray[$guildId] = clone($guildDetails);

            $guildDetails->generateEncounterDetails('dungeon', $dungeonId);

            if ( empty($guildDetails->_dungeonDetails->$dungeonId->_complete) ) { continue; }

            $progressionDetails = $guildDetails->_dungeonDetails->$dungeonId;

            $this->_dungeonGuildArray[$guildId]->mergeViewDetails('_dungeonDetails', $dungeonId);

            $sortArray[$progressionDetails->_complete][$guildId] = $progressionDetails->_recentTime;
        }

        return $sortArray;
    }

    /**
     * set the standings header and guild array per view
     * 
     * @param array   $sortGuildArray [ array of guilds ]
     * @param Dungeon $dungeonDetails [ dungeon data object ]
     * @param integer $guildLimit     [ maximum number of guilds ]
     *
     * @return object [ standings object array ]
     */
    private function _setViewStandingsArray($sortGuildArray, $dungeonDetails, $guildLimit) {
        $retVal         = new stdClass();
        $retVal->header = $dungeonDetails->_name . ' Top ' . $guildLimit . ' Standings';
        $retVal->data   = (!empty($sortGuildArray) ? $sortGuildArray : array());

        return $retVal;
    }

    /**
     * add guild to the temporary guild array
     * 
     * @param GuildDetails &$guildDetails        [ guild details detail object ]
     * @param array        &$temporaryGuildArray [ list of guilds ]
     * @param array        &$completionTimeArray [ list of completion times ]
     * @param integer      &$rankCount           [ guild rank increment ]
     *
     * @return void
     */
    private function _addGuildToListArray(&$guildDetails, &$temporaryGuildArray, &$completionTimeArray, &$rankCount) {
        $guildId = $guildDetails->_guildId;

        $guildDetails->getTimeDiff($completionTimeArray, $guildDetails->_strtotime);
        $guildDetails->_rank = $rankCount;

        $temporaryGuildArray[$guildId] = $guildDetails;
        $completionTimeArray           = $guildDetails->_strtotime;
        $rankCount++;
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

        $returnArray  = array();
        $tierDetails  = CommonDataContainer::$tierArray[LATEST_TIER];
        $dungeonLimit = 2;
        $regionLimit  = 1;
        $dungeonCount = 0;

        foreach ( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
            $dungeonDetails      = CommonDataContainer::$dungeonArray[$dungeonId];
            $temporarySortArray  = array();
            $sortGuildArray      = array();
            $completionTimeArray = 0;
            $rankArray           = 1;

            $temporarySortArray = $this->_getTemporarySortArray($dungeonDetails);

            if ( !empty($temporarySortArray) ) {
                krsort($temporarySortArray);

                foreach ( $temporarySortArray as $score => $temporaryGuildArray ) {
                    asort($temporaryGuildArray);

                    foreach ( $temporaryGuildArray as $guildId => $complete ) {
                        $guildDetails = $this->_dungeonGuildArray[$guildId];

                        if ( !isset($completionTimeArray) ) { $completionTimeArray = 0; }
                        if ( !isset($sortGuildArray) ) { $sortGuildArray = array(); }
                        if ( !isset($rankArray) ) { $rankArray = 1; }

                        $this->_addGuildToListArray($guildDetails, $sortGuildArray, $completionTimeArray, $rankArray);
                    }
                }
            }

            $returnArray[$dungeonId] = $this->_setViewStandingsArray($sortGuildArray, $dungeonDetails, $guildLimit);
            $dungeonCount++;
        }

        // Split into Regions
        if ( $content == 1 ) {
            $returnArray = $this->_convertStandingsToRegion($returnArray, $guildLimit);
        }

        // Cut off limit amount of guilds
        $returnArray = $this->_setLimitToStandingsArray($returnArray, $guildLimit);

        return $returnArray;
    }

    /**
     * sets the limit number of guilds in standings
     * 
     * @param  array   $standingsArray [ array of guilds ]
     * @param  integer $guildLimit     [ maximum number of guilds ]
     *
     * @return array [ array of guilds ]
     */
    private function _setLimitToStandingsArray($standingsArray, $guildLimit) {
        foreach( $standingsArray as $dungeonId => $dungeonTable ) {
            $standingsArray[$dungeonId]->data = array_slice($standingsArray[$dungeonId]->data, 0 , $guildLimit);
        }

        return $standingsArray;
    }

    /**
     * convert standings array to be region based
     * 
     * @param  array   $standingsArray [ array of guilds ]
     * @param  integer $guildLimit     [ maximum number of guilds ]
     * 
     * @return array [ array of guilds ]
     */
    private function _convertStandingsToRegion($standingsArray, $guildLimit) {
        $returnArray = array();

        foreach( $standingsArray as $dungeonId => $dungeonTable ) {
            $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
            $regionRank = array();
            $regionValue;

            foreach( $dungeonTable->data as $guildId => $guildDetails ) {
                $regionValue = $dungeonId . '-region-' .$guildDetails->_region;

                if ( !isset($returnArray[$regionValue]) ) {
                    $returnArray[$regionValue] = new stdClass();
                    $returnArray[$regionValue]->data = array();
                    $returnArray[$regionValue]->header = $dungeonDetails->_name . ' Top ' . $guildLimit . ' ' .$guildDetails->_region . ' Guilds';

                    $regionRank[$regionValue] = 1;
                }

                $guildDetails->_rank = $regionRank[$regionValue];
                $returnArray[$regionValue]->data[$guildId] = $guildDetails;

                $regionRank[$regionValue]++;
            }

            break;
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

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $guildDetails->generateRankDetails('dungeons');

            if ( !isset($guildDetails->_rankDetails->_rankDungeons) ) { continue; }

            foreach ( $guildDetails->_rankDetails->_rankDungeons as $dungeonDetails ) {
                $dungeonId = $dungeonDetails->_id;
                $points    = $dungeonDetails->_points;
                $system    = CommonDataContainer::$rankSystemArray[$dungeonDetails->_system]->_abbreviation;

                $dungeonStatsArray[$dungeonId][$system][$guildId] = $points;
            }
        }

        $tierDetails = CommonDataContainer::$tierArray[LATEST_TIER];
        foreach( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
            if ( $dungeonDetails->_type != 0 ) { continue; }

            $rankArray    = array();
            $detailsArray = array();

            $returnArray[$dungeonId]['name']         = $dungeonDetails->_name;
            $returnArray[$dungeonId]['abbreviation'] = strtolower($dungeonDetails->_abbreviation);
                            
            if ( !isset($dungeonStatsArray[$dungeonId]) ) { continue; }

            foreach ( $dungeonStatsArray[$dungeonId] as $systemId => $pointsArray ) {
                foreach ( $pointsArray as $guildId => $points ) {
                    $rankArray[$systemId][$guildId] = $points;
                }
            }

            foreach ( $rankArray as $systemId => $guildArray ) {
                arsort($guildArray);

                $rankArray[$systemId] = $guildArray;
            }
            foreach ( $rankArray as $systemId => $guildArray ) {
                $rank = 0;

                foreach ( $guildArray as $guildId => $points ) {
                    if ( $rank == $limit ) { break; }

                    $guildDetails = CommonDataContainer::$guildArray[$guildId];
                    $rankDetails  = $guildDetails->_rankDetails->_rankDungeons->{$dungeonId . '_' . $systemId};
                    $points       = Functions::formatPoints($points);
                    $trend        = $rankDetails->_trend->_world;
                    $image        = Functions::getTrendImage($trend);
                    $identifier   = $guildId . ' | ' . $systemId;
                    $guildDetails->nameLength(0);
                    $rank++;

                    $detailsArray[$rank][$identifier]           = new stdClass();
                    $detailsArray[$rank][$identifier]->points   = $points;
                    $detailsArray[$rank][$identifier]->progress = $guildDetails->_dungeonDetails->$dungeonId->_standing;
                    $detailsArray[$rank][$identifier]->guild    = $guildDetails->_nameLink;
                    $detailsArray[$rank][$identifier]->rank     = $image . ' ' . $rank;
                }
            }



            $returnArray[$dungeonId]['data'] = $detailsArray;
        }

        return $returnArray;
    }

    /**
     * get the most recent submitted encounters sorted by kill date
     * 
     * @param  integer $limit  [ maximum number of encounter entries ]
     * 
     * @return array [ array of encounter kill data entries ]
     */
    private function _getRecentRaids($limit) {
        $dbh       = DbFactory::getDbh();
        $dataArray = array();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
           ORDER BY strtotime DESC
              LIMIT %s", 
                    DbFactory::TABLE_RECENT_RAIDS, 
                    $limit
                ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId     = $row['guild_id'];
            $encounterId = $row['encounter_id'];
            $identifier  = $guildId . '|' . $encounterId;

            if ( !isset(CommonDataContainer::$guildArray[$guildId]) ) { continue; }

            $guildDetails = CommonDataContainer::$guildArray[$guildId];
            $guildDetails->generateEncounterDetails('encounter', $encounterId);

            if ( !isset($guildDetails->_encounterDetails->$encounterId) ) { continue; }

            $encounterDetails   = $guildDetails->_encounterDetails->$encounterId;
            $encounterSpecifics = CommonDataContainer::$encounterArray[$encounterId];
            $guildDetails->nameLength(12);

            $dataArray[$identifier]             = new stdClass();
            $dataArray[$identifier]->name       = $guildDetails->_name;
            $dataArray[$identifier]->guild      = $guildDetails->_nameLink;
            $dataArray[$identifier]->encounter  = Functions::shortName($encounterSpecifics->_name, 22);
            $dataArray[$identifier]->time       = $encounterDetails->_shorttime;
            $dataArray[$identifier]->server     = $guildDetails->_server;
            $dataArray[$identifier]->link       = Functions::generateInternalHyperLink('guild', $guildDetails->_faction, $guildDetails->_server, $guildDetails->_name, 0, false);
            $dataArray[$identifier]->screenshot = $encounterDetails->_screenshotLink;
            $dataArray[$identifier]->video      = $encounterDetails->_videoLink;
        }

        return $dataArray;
    }

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
            "SELECT * 
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
            "SELECT *
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
            "SELECT *
               FROM %s
              WHERE active = 1
              LIMIT %s", 
                    DbFactory::TABLE_TWITCH,
                    $limit
                ));
        $query->execute();

        return $query;
    }
}