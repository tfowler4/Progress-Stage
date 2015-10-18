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
    protected $_encounter;
    
    protected $_detailsPane;
    protected $_dataDetails;

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
            'Trending'      => '_trendImage',
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

        $guildListing = new Listings('rankings', $params);

        if ( isset($params[0]) ) { $this->_view     = $params[0]; }
        if ( isset($params[1]) ) { $this->_rankType = $params[1]; }
        if ( isset($params[2]) ) { $this->_tier     = $params[2]; }
        if ( isset($params[3]) ) { $this->_dungeon  = $params[3]; }
        if ( isset($params[4]) ) { $this->_encounter = $params[4]; }

        switch($this->_view) {
            case 'world':
                if ( isset($guildListing->listArray->world['world']) ) {
                    $this->_rankingsArray['world'] = $guildListing->listArray->world['world'];
                    $this->_rankingsArray['world']->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                    $this->_rankingsArray['world']->headerText  = 'World Rankings';
                }
                break;
            case 'region':
                foreach( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $name         = $regionDetails->_name;
                    $abbreviation = $regionDetails->_abbreviation;
                    $style        = $regionDetails->_style;

                    if ( isset($guildListing->listArray->region[$abbreviation]) ) {
                        $this->_rankingsArray[$abbreviation] = $guildListing->listArray->region[$abbreviation];
                        $this->_rankingsArray[$abbreviation]->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                        $this->_rankingsArray[$abbreviation]->headerText  = $style . ' Rankings';
                    }
                }
                break;
            case 'server':
                foreach ( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
                    $server = $serverDetails->_name;
                    $region = $serverDetails->_region;

                    if ( isset($guildListing->listArray->server[$server]) ) {
                        $this->_rankingsArray[$server] = $guildListing->listArray->server[$server];
                        $this->_rankingsArray[$server]->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                        $this->_rankingsArray[$server]->headerText  = $server . ' Rankings';
                    }
                }
                break;
        }

        $this->_setRankSystemDetails();
        $this->_dataDetails    = $guildListing->_dataDetails;
        $this->_detailsPane    = $this->_dataDetails;
        $this->_topGuildsArray = $guildListing->_topGuildsArray;
        $this->_dataDetails->setClears();

        $this->title = $this->_dataDetails->_name . ' ' . ucfirst($this->_view) . ' ' . self::PAGE_TITLE;
    }

    private function _setRankSystemDetails() {
        foreach( $this->_rankingsArray as $listType => $dataArray ) {

            $currentPoints = 0;
            foreach( $dataArray->data as $guildId => $guildDetails ) {
                $rankDetails = $guildDetails->{'_' . strtolower($this->_rankType)};
                $guildDetails->_rank      = $rankDetails->_rank->{'_' . $this->_view};
                $guildDetails->_trend     = $rankDetails->_trend->{'_' . $this->_view};
                $guildDetails->_prevRank  = $rankDetails->_prevRank->{'_' . $this->_view};
                $points                   = $rankDetails->_points;
                $guildDetails->_pointDiff = Functions::getPointDiff($currentPoints, $points);
                $guildDetails->_points    = number_format($rankDetails->_points, 2, '.', ',');

                // set the trend image based on trend value
                $guildDetails->_trendImage = $guildDetails->_trend;

                if ( $guildDetails->_trend > 0 ) {
                    $guildDetails->_trendImage = IMG_ARROW_TREND_UP_SML . ' ' . $guildDetails->_trend;
                }

                if ( $guildDetails->_trend < 0 ) {
                    $guildDetails->_trendImage = IMG_ARROW_TREND_DOWN_SML . ' ' . $guildDetails->_trend;
                }

                $currentPoints = $points;
            }
        }
    }

    /**
     * set the data table header fields to be displayed
     * 
     * @param string $tier      [ tier parameter ]
     * @param string $dungeon   [ dungeon parameter ]
     * @param string $encounter [ encounter parameter ]
     *
     * @return void
     */
    private function _setTableFields($tier, $dungeon, $encounter) {
        $tableFields = array();

        if ( !empty($encounter) ) {
            $tableFields = self::TABLE_HEADER_DEFAULT;
        } elseif ( !empty($dungeon) ) {
            $tableFields = self::TABLE_HEADER_DEFAULT;
        } elseif ( !empty($tier) ) {
            $tableFields = self::TABLE_HEADER_DEFAULT;
        }

        return $tableFields;
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