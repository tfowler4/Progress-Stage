<?php
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
    const PAGE_DESCRIPTION      = GAME_NAME_1 . '\s #1 Resource for raid progression tracking.';
    const LIMIT_NEWS            = 3;
    const LIMIT_RECENT_RAIDS    = 56;
    const LIMIT_GUILD_RANKINGS  = 10;
    const LIMIT_GUILD_STANDINGS = 10;
    const STANDINGS_DISPLAY     = 1;

    const HEADER_STANDINGS = array(
            'Rank'     => '_rank',
            'Guild'    => '_nameLink',
            'Server'   => '_serverLink',
            'Progress' => '_standing'
        );

    public function __construct($module, $article) {
        parent::__construct($module);

        $this->title       = self::PAGE_TITLE;
        $this->description = self::PAGE_DESCRIPTION;

        if ( isset($params[0]) ) { $this->_article = $article; }

        $this->_article = strtolower(str_replace("_"," ", $this->_article)); 
        $this->_article = strtolower(str_replace("poundsign","#", $this->_article));

        $this->_videoLinks     = $this->getLiveVideos(); //Not Ready for Launch
        $this->_guildRankings  = $this->getRankings(POINT_SYSTEM_DEFAULT, self::LIMIT_GUILD_RANKINGS);
        $this->_guildStandings = $this->getStandings(self::STANDINGS_DISPLAY, self::LIMIT_GUILD_STANDINGS);
        $this->_recentRaids    = $this->getRecentRaids(self::LIMIT_RECENT_RAIDS);
        $this->_newsArticles   = $this->getArticles($this->article, self::LIMIT_NEWS);

        $this->_standingsTableHeader = self::HEADER_STANDINGS;
    }

    public function getArticles($article, $limit) {
        $dataArray = array();
        $query;

        if ( strlen($article) > 0 ) { 
            $query  = $this->getNewsArticle($article); 
        } else {
            $query = $this->getNews($limit);
        }

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $row['date_added']          = Functions::formatDate($row['date_added'], 'm-d-Y H:i');
            $article                    = new Article($row);
            $dataArray[$article->date]  = $article;
        }

        return $dataArray;
    }

    public function getLiveVideos() {
        $dataArray = array();
        $query;

        $query  = $this->getTwitchChannels(); 

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $dataArray[$row['twitch_id']] = new TwitchDetails($row);
        }

        return $dataArray;
    }

    public function getTemporarySortArray($dungeonDetails) {
        $sortArray                = array();
        $this->_dungeonGuildArray = array();
        $dungeonId                = $dungeonDetails->_dungeonId;

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $this->_dungeonGuildArray[$guildId] = clone($guildDetails);

            //$guildDetails->generateEncounterDetails('dungeon', $dungeonId);

            if ( empty($guildDetails->_dungeonDetails->$dungeonId->_complete) ) { continue; }

            $progressionDetails = $guildDetails->_dungeonDetails->$dungeonId;

            $this->_dungeonGuildArray[$guildId]->mergeViewDetails('_dungeonDetails', $dungeonId);

            $sortArray[$progressionDetails->_complete][$guildId] = $progressionDetails->_recentTime;
        }

        return $sortArray;
    }

    public function setViewStandingsArray($viewType, $sortGuildArray, $dungeonDetails, $guildLimit) {
        $retVal         = new stdClass();
        $retVal->header = $dungeonDetails->_name . ' Top ' . $guildLimit . ' Standings';
        $retVal->data   = (!empty($sortGuildArray) ? $sortGuildArray : array());

        return $retVal;
    }

    public function addGuildToListArray(&$guildDetails, &$temporaryGuildArray, &$completionTimeArray, &$rankArray) {
        $guildId = $guildDetails->_guildId;

        $guildDetails->getTimeDiff($completionTimeArray, $guildDetails->_strtotime);
        $guildDetails->_rank = $rankArray;

        $temporaryGuildArray[$guildId] = $guildDetails;
        $completionTimeArray           = $guildDetails->_strtotime;
        $rankArray++;
    }

    public function getStandings($content, $guildLimit) {
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
            if ( $dungeonCount > 1 ) { break; }

            $dungeonDetails      = CommonDataContainer::$dungeonArray[$dungeonId];
            $temporarySortArray  = array();
            $sortGuildArray      = array();
            $completionTimeArray = 0;
            $rankArray           = 1;

            $temporarySortArray = $this->getTemporarySortArray($dungeonDetails);

            if ( !empty($temporarySortArray) ) {
                krsort($temporarySortArray);

                foreach ( $temporarySortArray as $score => $temporaryGuildArray ) {
                    asort($temporaryGuildArray);

                    foreach ( $temporaryGuildArray as $guildId => $complete ) {
                        $guildDetails = $this->_dungeonGuildArray[$guildId];

                        if ( !isset($completionTimeArray) ) { $completionTimeArray = 0; }
                        if ( !isset($sortGuildArray) ) { $sortGuildArray = array(); }
                        if ( !isset($rankArray) ) { $rankArray = 1; }

                        $this->addGuildToListArray($guildDetails, $sortGuildArray, $completionTimeArray, $rankArray);
                    }
                }
            }

            $returnArray[$dungeonId] = $this->setViewStandingsArray($this->_view, $sortGuildArray, $dungeonDetails, $guildLimit);
            $dungeonCount++;
        }

        // Split into Regions
        if ( $content == 1 ) {
            $returnArray = $this->convertStandingsToRegion($returnArray, $guildLimit);
        }

        // Cut off limit amount of guilds
        $returnArray = $this->setLimitToStandingsArray($returnArray, $guildLimit);

        return $returnArray;
    }

    public function setLimitToStandingsArray($standingsArray, $guildLimit) {
        foreach( $standingsArray as $dungeonId => $dungeonTable ) {

            if ( count($standingsArray[$dungeonId]->data) < $guildLimit ) { 
                //$guildLimit = count($standingsArray[$dungeonId]->data); 
            }

            $standingsArray[$dungeonId]->data = array_slice($standingsArray[$dungeonId]->data, 0 , $guildLimit);
        }

        return $standingsArray;
    }

    public function convertStandingsToRegion($standingsArray, $guildLimit) {
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

    public function getRankings($point_system, $limit) {
        $returnArray        = array();
        $dungeonStatsArray  = array();

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $guildDetails->generateRankDetails('dungeons');
            $guildDetails->generateEncounterDetails('');

            if ( !isset($guildDetails->_rankDetails->_rankDungeons) ) { continue; }

            foreach ( $guildDetails->_rankDetails->_rankDungeons as $dungeonDetails ) {
                $dungeonId  = $dungeonDetails->_id;
                $points     = $dungeonDetails->_points;
                $system     = CommonDataContainer::$rankSystemArray[$dungeonDetails->_system]->_abbreviation;

                $dungeonStatsArray[$dungeonId][$system][$guildId] = $points;
            }
        }

        foreach( CommonDataCOntainer::$tierArray as $tierId => $tierDetails ) {
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
                        $guildDetails->nameLength(0);
                        $rank++;

                        $detailsArray[$systemId][$guildId]           = new stdClass();
                        $detailsArray[$systemId][$guildId]->points   = $points;
                        $detailsArray[$systemId][$guildId]->progress = $guildDetails->_dungeonDetails->$dungeonId->_standing;
                        $detailsArray[$systemId][$guildId]->guild    = $guildDetails->_nameLink;
                        $detailsArray[$systemId][$guildId]->rank     = $image . ' ' . $rank;
                    }
                }

                $returnArray[$dungeonId]['data'] = $detailsArray;
            }
        }

        return $returnArray;
    }

    public function getRecentRaids($limit) {
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
            //$guildDetails->generateEncounterDetails('');

            if ( !isset($guildDetails->_encounterDetails->$encounterId) ) { continue; }

            $encounterDetails   = $guildDetails->_encounterDetails->$encounterId;
            $encounterSpecifics = CommonDataContainer::$encounterArray[$encounterId];
            $guildDetails->nameLength(12);

            $dataArray[$identifier]            = new stdClass();
            $dataArray[$identifier]->name      = $guildDetails->_name;
            $dataArray[$identifier]->guild     = $guildDetails->_nameLink;
            $dataArray[$identifier]->encounter = Functions::shortName($encounterSpecifics->_name, 22);
            $dataArray[$identifier]->time      = $encounterDetails->_shorttime;
            $dataArray[$identifier]->server    = $guildDetails->_server;
        }

        return $dataArray;
    }

    public function getNews($limit) {
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

    public function getNewsArticle($articleTitle) {
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

    public function getTwitchChannels() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE active = 1
              LIMIT 10", 
                    DbFactory::TABLE_TWITCH
                ));
        $query->execute();

        return $query;
    }
}