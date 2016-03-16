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
            array('header' => 'Rank',          'key' => '_rank',       'class' => 'text-center'),
            array('header' => 'Guild',         'key' => '_nameLink',   'class' => ''),
            array('header' => 'Server',        'key' => '_serverLink', 'class' => 'hidden-xs'),
            array('header' => 'Progress',      'key' => '_progress',   'class' => 'text-center border-left hidden-xs hidden-sm'),
            array('header' => 'Points',        'key' => '_points',     'class' => 'text-center'),
            array('header' => 'Diff',          'key' => '_pointDiff',  'class' => 'text-center hidden-xs hidden-sm'),
            array('header' => 'WF',            'key' => '_worldFirst', 'class' => 'text-center border-left hidden-xs'),
            array('header' => 'RF',            'key' => '_regionFirst','class' => 'text-center hidden-xs'),
            array('header' => 'SF',            'key' => '_serverFirst','class' => 'text-center hidden-xs'),
            array('header' => 'Trending',      'key' => '_trendImage', 'class' => 'text-center border-left hidden-xs hidden-sm hidden-md'),
            array('header' => 'Previous Rank', 'key' => '_prevRank',   'class' => 'text-center hidden-xs hidden-sm hidden-md')
        );

    const PANE_DUNGEON = array(
            'Name'         => '_name',
            'Tier'         => '_tierFullTitle',
            'Raid Size'    => '_raidSize',
            'Encounters'   => '_numOfEncounters',
            'Release Date' => '_dateLaunch',
            'EU Time Diff' => '_euTimeDiffTitle',
            'WW Clears'    => '_numOfDungeonClears',
            'NA Clears'    => '_numOfNADungeonClears',
            'EU Clears'    => '_numOfEUDungeonClears',
            'First Clear'  => '_firstDungeonClear',
            'Recent Clear' => '_recentDungeonClear'
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
        $this->_dataDetails    = $guildListing->_dataDetails;

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

                    $this->_dataDetails->setClears($this->_rankingsArray['world']->data);
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

                        $this->_dataDetails->setClears($this->_rankingsArray[$abbreviation]->data);
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

                        $this->_dataDetails->setClears($this->_rankingsArray[$server]->data);
                    }
                }
                break;
        }

        $this->_setRankSystemDetails();
        $this->_detailsPane    = $this->_dataDetails;
        $this->_topGuildsArray = $guildListing->_topGuildsArray;

        $this->title = $this->_dataDetails->_name . ' ' . ucfirst($this->_view) . ' ' . self::PAGE_TITLE;
    }

    private function _setRankSystemDetails() {
        foreach( $this->_rankingsArray as $listType => $dataArray ) {

            $currentPoints = 0;
            foreach( $dataArray->data as $guildId => $guildDetails ) {
                $guildDetails->_pointDiff = Functions::getPointDiff($currentPoints, $guildDetails->_points);

                $currentPoints = $guildDetails->_points;

                $guildDetails->_points = Functions::formatPoints($guildDetails->_points);
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
     * @param  string  $class  [ custom classes ]
     * 
     * @return string [ html hyperlink ]
     */
    public function generateInternalHyperLink($view, $system, $text, $class) {
        $url       = PAGE_RANKINGS . $view;
        $hyperlink = '';

        if ( isset($system) ) { $url         .= '/' . $system; }
        if ( isset($this->_tier) ) { $url    .= '/' . $this->_tier; }
        if ( isset($this->_dungeon) ) { $url .= '/' . $this->_dungeon; }

        if (!empty($class)) {
            $class = 'class="' . $class . '"';
        }

        $hyperlink = '<a ' . $class . ' href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}