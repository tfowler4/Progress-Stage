<?php

/**
 * point rankings guild listing page
 */
class RankingsModel extends Model {
    protected $_rankingsArray = array();
    protected $_topGuildsArray = array();
    protected $_rankType;
    protected $_view;
    protected $_tier;
    protected $_dungeon;
    protected $_detailsPane;
    protected $_dataDetails;
    protected $_dataType;
    protected $_rankingId;
    protected $_rankingsType;
    protected $_standingsType;
    protected $_tableHeader;

    const LIMIT_TREND_UNRANK = 10;
    const LIMIT_TREND_RANK   = 10;
    const LIMIT_TREND        = 10;
    const LIMIT_TREND_TOTAL  = 15;

    const TABLE_HEADER_DEFAULT = array(
            'Rank'          => '_rank',
            'Guild'         => '_nameLink',
            'Server'        => '_serverLink',
            'Progress'      => '_standing',
            'Points'        => '_points',
            'Diff'          => '_pointDiff',
            'WF'            => '_worldFirst',
            'RF'            => '_regionFirst',
            'SF'            => '_serverFirst',
            'Trending'      => '_trend',
            'Previous Rank' => '_prevRank'
        );

    const PANE_DUNGEON = array(
            'Name'              => '_name',
            'Tier'              => '_tierFullTitle',
            'Raid Size'         => '_raidSize',
            'Encounters'        => '_numOfEncounters',
            'Release Date'      => '_dateLaunch',
            'EU Time Diff'      => '_euTimeDiffTitle',
            'WW Clears'         => '_numOfDungeonClears',
            'NA Clears'         => '_numOfNADungeonClears',
            'EU Clears'         => '_numOfEUDungeonClears',
            'First Clear'       => '_firstDungeonClear',
            'Most Recent Clear' => '_recentDungeonClear'
        );

    const PANE_TIER = array(
            'Name'              => '_name',
            'Tier'              => '_tierFullNum',
            'Encounters'        => '_numOfEncounters',
            'Starting Date'     => '_dateStart',
            'Ending Date'       => '_dateEnd',
            'WW Clears'         => '_numOfTierClears',
            'NA Clears'         => '_numOfNATierClears',
            'EU Clears'         => '_numOfEUTierClears',
            'First Clear'       => '_firstTierClear',
            'Most Recent Clear' => '_recentTierClear'
        );

    const GLOSSARY = array(
            'WF' => 'World Firsts',
            'RF' => 'Region Firsts',
            'SF' => 'Server Firsts',
            'WR' => 'World Rank',
            'RR' => 'Region Rank',
            'SR' => 'Server Rank'
        );

    const PAGE_TITLE = 'Point Rankings';
    const PAGE_NAME  = 'Rankings';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;
        
        if ( isset($params[0]) ) { $this->_view     = $params[0]; }
        if ( isset($params[1]) ) { $this->_rankType = $params[1]; }
        if ( isset($params[2]) ) { $this->_tier     = $params[2]; }
        if ( isset($params[3]) ) { $this->_dungeon  = $params[3]; }

        $this->_getDataDetails($this->_rankType, $this->_tier, $this->_dungeon);

        $this->_detailsPane   = $this->_dataDetails;
        $this->_rankingsArray = $this->_getRankings($this->_rankType, $this->_view, $this->_tier, $this->_dungeon);
        $this->_dataDetails->setClears();

        $this->title .= ' Rankings';
    }

    /**
     * get model data details based on url parameters
     * 
     * @param  string $rankType  [ name of rank system ]
     * @param  string $tier      [ name of tier ]
     * @param  string $dungeon   [ name of dungeon ]
     * 
     * @return mixed [ null if dataDetails are empty ]
     */
    private function _getDataDetails($rankType, $tier, $dungeon) {
        $systemDetails = Functions::getRankSystemByName($rankType);
        $systemId      = $systemDetails->_abbreviation;

        if ( !empty($this->_dungeon) ) {
            $this->_rankingsType  = '_rankDungeons';
            $this->_standingsType = '_dungeonDetails';
            $this->_tableHeader   = self::TABLE_HEADER_DEFAULT;
            $this->_dataDetails   = Functions::getDungeonByName($this->_dungeon);

            if ( empty($this->_dataDetails) ) { return null; }

            $this->_standingsId = $this->_dataDetails->_dungeonId;
            $this->_rankingId   = $this->_standingsId . '_' . $systemId;
            $this->title        = $this->_dataDetails->_name;
        } elseif ( !empty($this->_tier) ) {
            $this->_rankingsType  = '_rankTiers';
            $this->_standingsType = '_tierDetails';
            $this->_tableHeader   = self::TABLE_HEADER_DEFAULT;
            $this->_dataDetails   = Functions::getTierByName($this->_tier);

            if ( empty($this->_dataDetails) ) { return null; }

            $this->_standingsId = $this->_dataDetails->_tier;
            $this->_rankingId   = $this->_standingsId . '_' . $systemId;
            $this->title        = $this->_dataDetails->_name;
        }

        if ( empty($this->_dataDetails) ) { Functions::sendTo404(); }
    }

    /**
     * get sorted guild array based on number of encounters completed in dungeon
     *
     * @return array [ sorted guild array by amount completed ]
     */
    private function _getTemporarySortArray() {
        $sortArray = array();

        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            $guildDetails->generateEncounterDetails('dungeon', $this->_standingsId);
            $guildDetails->generateRankDetails('dungeons');

            if ( !isset($guildDetails->_rankDetails->{$this->_rankingsType}->{$this->_rankingId}) ) { continue; }

            $points = $guildDetails->_rankDetails->{$this->_rankingsType}->{$this->_rankingId}->_points;

            $sortArray[$guildId] = $points;
        }

        return $sortArray;
    }

    /**
     * get guild rankings based upon type of content selected
     * 
     * @param  string $rankType [ point ranking system ]
     * @param  string $view     [ view type filter ]
     * @param  string $tier     [ name of tier ]
     * @param  string $dungeon  [ name of dungeon ]
     * 
     * @return array [ array of guilds sorted by points ]
     */
    private function _getRankings($rankType, $view, $tier, $dungeon) {
        $returnArray         = array();
        $temporarySortArray  = array();
        $completionTimeArray = array();
        $sortGuildArray      = array();
        $pointDiffArray      = array();

        $temporarySortArray = $this->_getTemporarySortArray();

        if ( !empty($temporarySortArray) ) {
            arsort($temporarySortArray);

            foreach ( $temporarySortArray as $guildId => $points ) {
                CommonDataContainer::$guildArray[$guildId]->mergeViewDetails($this->_standingsType, $this->_standingsId);
                CommonDataContainer::$guildArray[$guildId]->mergeRankViewDetails($this->_rankingsType, $this->_rankingId, $this->_view);

                if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = CommonDataContainer::$guildArray[$guildId]; }

                $guildDetails = CommonDataContainer::$guildArray[$guildId];
                $server       = $guildDetails->_server;
                $region       = $guildDetails->_region;
                $country      = $guildDetails->_country;

                switch ( $this->_view ) {
                    case 'world':
                        if ( !isset($pointDiffArray['world']) ) { $pointDiffArray['world'] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['world'], $guildDetails->_points);

                        $sortGuildArray['world'][$guildId] = $guildDetails;
                        $pointDiffArray['world']           = $guildDetails->_points;
                        break;
                    case 'region':
                        if ( !isset($pointDiffArray['region'][$region]) ) { $pointDiffArray['region'][$region] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['region'][$region], $guildDetails->_points);

                        $sortGuildArray['region'][$region][$guildId] = $guildDetails;
                        $pointDiffArray['region'][$region] = $guildDetails->_points;
                        break;
                    case 'server':
                        if ( !isset($pointDiffArray['server'][$server]) ) { $pointDiffArray['server'][$server] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['server'][$server], $guildDetails->_points);

                        $sortGuildArray['server'][$server][$guildId] = $guildDetails;
                        $pointDiffArray['server'][$server] = $guildDetails->_points;
                        break;
                    case 'country':
                        if ( !isset($pointDiffArray['country'][$country]) ) { $pointDiffArray['country'][$country] = 0; }

                        $guildDetails->getPointDiff($pointDiffArray['country'][$country], $guildDetails->_points);

                        $sortGuildArray['country'][$country][$guildId]  = $guildDetails;
                        $pointDiffArray['country'][$country] = $guildDetails->_points;
                        break;
                }

                CommonDataContainer::$guildArray[$guildId]->_points = Functions::formatPoints($points);
            }
        }

        $returnArray = $this->_setViewRankingsArray($this->_view, $sortGuildArray);

        return $returnArray;
    }

    /**
     * set the rankings header and guild array per view
     *
     * @param string $viewType       [ view filter ex. server/region ]
     * @param array  $sortGuildArray [ array of guilds ]
     *
     * @return object [ standings object array ]
     */
    private function _setViewRankingsArray($viewType, $sortGuildArray) {
        $retVal = new stdClass();

        switch( $this->_view ) {
            case 'world':
                $retVal->world         = new stdClass();
                $retVal->world->header = ucfirst($this->_view) . ' ' . self::PAGE_NAME;
                $retVal->world->data   = (!empty($sortGuildArray['world']) ? $sortGuildArray['world'] : array());

                $this->title .= ' World';
                break;
            case 'region':
                foreach ( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $region       = $regionDetails->_name;
                    $abbreviation = $regionDetails->_abbreviation;

                    $retVal->$abbreviation         = new stdClass();
                    $retVal->$abbreviation->header = $regionDetails->_style . ' ' . self::PAGE_NAME;
                    $retVal->$abbreviation->data   = (!empty($sortGuildArray['region'][$abbreviation]) ? $sortGuildArray['region'][$abbreviation] : array());
                }

                $this->title .= ' Region';
                break;
            case 'server':
                foreach ( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
                    $server = $serverDetails->_name;
                    $region = $serverDetails->_region;

                    $retVal->$server         = new stdClass();
                    $retVal->$server->header = $server . ' ' . self::PAGE_NAME;
                    $retVal->$server->data   = (!empty($sortGuildArray['server'][$server]) ? $sortGuildArray['server'][$server] : array());
                }

                $this->title .= ' Server'; 
                break;
            case 'country':
                foreach ( CommonDataContainer::$countryArray as $countryId => $countryDetails ) {
                    $country = $countryDetails->_name;
                    $region  = $countryDetails->_region;

                    $retVal->$country         = new stdClass();
                    $retVal->$country->header = $country . ' ' . self::PAGE_NAME;
                    $retVal->$country->data   = (!empty($sortGuildArray['country'][$country]) ? $sortGuildArray['country'][$country] : array());
                }

                $this->title .= ' Country';
                break;
        }

        return $retVal;
    }

    /**
     * generate model specific internal links
     * 
     * @param  string  $view   [ view type filter ]
     * @param  string  $system [ point ranking system ]
     * @param  string  $text   [ display text ]
     * 
     * @return string [ html hyperlink ]
     */
    public function generateInternalHyperLink($view, $system, $text) {
        $url       = PAGE_RANKINGS . $view;
        $hyperlink = '';

        if ( isset($system) ) { $url         .= '/' . $system; }
        if ( isset($this->_tier) ) { $url    .= '/' . $this->_tier; }
        if ( isset($this->_dungeon) ) { $url .= '/' . $this->_dungeon; }

        $hyperlink = '<a href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}