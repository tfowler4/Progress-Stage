<?php

/**
 * progression standings guild listing page
 */
class StandingsModel extends Model {
    protected $_standingsArray = array();
    protected $_topGuildsArray = array();

    protected $_view;
    protected $_tier;
    protected $_dungeon;
    protected $_encounter;
    protected $_identifier;

    protected $_detailsPane;
    protected $_dataDetails;

    const GLOSSARY = array(
            'WF' => 'World Firsts',
            'RF' => 'Region Firsts',
            'SF' => 'Server Firsts',
            'WR' => 'World Rank',
            'RR' => 'Region Rank',
            'SR' => 'Server Rank'
        );

    const TABLE_HEADER_STANDINGS_DUNGEON = array(
            array('header' => 'Rank',            'key' => '_rank',            'class' => 'text-center'),
            array('header' => 'Guild',           'key' => '_nameLink',        'class' => ''),
            array('header' => 'Server',          'key' => '_serverLink',      'class' => ''),
            array('header' => 'Progress',        'key' => '_progress',        'class' => 'text-center border-left'),
            array('header' => 'Hard Modes',      'key' => '_specialProgress', 'class' => 'text-center hidden-xs hidden-sm'),
            array('header' => 'Conqueror',       'key' => '_achievement',     'class' => 'text-center hidden-xs hidden-sm'),
            array('header' => 'WF',              'key' => '_worldFirst',      'class' => 'text-center border-left hidden-xs'),
            array('header' => 'RF',              'key' => '_regionFirst',     'class' => 'text-center hidden-xs'),
            array('header' => 'SF',              'key' => '_serverFirst',     'class' => 'text-center hidden-xs'),
            array('header' => 'Trending',        'key' => '_trendImage',      'class' => 'text-center border-left hidden-xs'),
            array('header' => 'Recent Activity', 'key' => '_recentActivity',  'class' => 'text-center border-left hidden-xs hidden-sm hidden-md')
        );

    const TABLE_HEADER_STANDINGS_ENCOUNTER = array(
            array('header' => 'Rank',            'key' => '_rank',            'class' => 'text-center'),
            array('header' => 'Guild',           'key' => '_nameLink',        'class' => ''),
            array('header' => 'Server',          'key' => '_serverLink',      'class' => 'hidden-xs'),
            array('header' => 'Date Completed',  'key' => '_datetime',        'class' => 'text-center border-left'),
            array('header' => 'Time Difference', 'key' => '_timeDiff',        'class' => 'text-center hidden-xs hidden-sm'),
            array('header' => 'Server Achieved', 'key' => '_killServer',      'class' => 'text-center hidden-xs hidden-sm'),
            array('header' => 'WF',              'key' => '_worldRankImage',  'class' => 'text-center border-left hidden-xs'),
            array('header' => 'RF',              'key' => '_regionRankImage', 'class' => 'text-center hidden-xs'),
            array('header' => 'SF',              'key' => '_serverRankImage', 'class' => 'text-center hidden-xs'),
            array('header' => 'Kill Video',      'key' => '_videoLink',       'class' => 'text-center border-left hidden-xs hidden-sm'),
            array('header' => 'Screenshot',      'key' => '_screenshotLink',  'class' => 'text-center hidden-xs hidden-sm')
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

    const PANE_ENCOUNTER = array(
            'Name'        => '_name',
            'Tier'        => '_tierFullTitle',
            'Dungeon'     => '_dungeon',
            'Raid Size'   => '_raidSize',
            'WW Clears'   => '_numOfEncounterKills',
            'NA Clears'   => '_numOfNAEncounterKills',
            'EU Clears'   => '_numOfEUEncounterKills',
            'First Kill'  => '_firstEncounterKill',
            'Recent Kill' => '_recentEncounterKill'
        );

    const PAGE_TITLE = 'Progression Standings';
    const PAGE_NAME  = 'Standings';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $guildListing       = new Listings('standings', $params);
        $this->_dataDetails = $guildListing->_dataDetails;

        if ( isset($params[0]) ) { $this->_view    = $params[0]; }
        if ( isset($params[1]) ) { $this->_tier    = $params[1]; }
        if ( isset($params[2]) ) { $this->_dungeon = $params[2]; }
        if ( isset($params[3]) ) { $this->_encounter = $params[3]; }

        switch($this->_view) {
            case 'world':
                if ( isset($guildListing->listArray->world['world']) ) {
                    $this->_standingsArray['world'] = $guildListing->listArray->world['world'];
                    $this->_standingsArray['world']->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                    $this->_standingsArray['world']->headerText  = 'World Standings';

                    $this->_dataDetails->setClears($this->_standingsArray['world']->data);
                }
                break;
            case 'region':
                foreach( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $name         = $regionDetails->_name;
                    $abbreviation = $regionDetails->_abbreviation;
                    $style        = $regionDetails->_style;

                    if ( isset($guildListing->listArray->region[$abbreviation]) ) {
                        $this->_standingsArray[$abbreviation] = $guildListing->listArray->region[$abbreviation];
                        $this->_standingsArray[$abbreviation]->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                        $this->_standingsArray[$abbreviation]->headerText  = $style . ' Standings';

                        $this->_dataDetails->setClears($this->_standingsArray[$abbreviation]->data);
                    }
                }
                break;
            case 'server':
                foreach ( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
                    $server = $serverDetails->_name;
                    $region = $serverDetails->_region;

                    if ( isset($guildListing->listArray->server[$server]) ) {
                        $this->_standingsArray[$server] = $guildListing->listArray->server[$server];
                        $this->_standingsArray[$server]->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                        $this->_standingsArray[$server]->headerText  = $server . ' Standings';

                        $this->_dataDetails->setClears($this->_standingsArray[$server]->data);
                    }
                }
                break;
            case 'country':
                foreach ( CommonDataContainer::$countryArray as $countryId => $countryDetails ) {
                    $country = $countryDetails->_name;

                    if ( isset($guildListing->listArray->country[$country]) ) {
                        $this->_standingsArray[$country] = $guildListing->listArray->country[$country];
                        $this->_standingsArray[$country]->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                        $this->_standingsArray[$country]->headerText  = $country . ' Standings';

                        $this->_dataDetails->setClears($this->_standingsArray[$country]->data);
                    }
                }
                break;
        }

        if ( $this->_encounter ) {
            $this->_setEncounterTimeDiffField();
        }

        $this->_detailsPane    = $this->_dataDetails;
        $this->_topGuildsArray = $guildListing->_topGuildsArray;
        $this->_identifier     = $guildListing->_identifier;

        $this->title = $this->_dataDetails->_name . ' ' . ucfirst($this->_view) . ' ' . self::PAGE_TITLE;
    }

    private function _setEncounterTimeDiffField() {
        foreach( $this->_standingsArray as $listType => $dataArray ) {

            $currentTime = 0;
            foreach( $dataArray->data as $guildId => $guildDetails ) {
                $time = $guildDetails->_strtotime;
                $guildDetails->getTimeDiff($currentTime, $time);

                $currentTime = $time;
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
            $tableFields = self::TABLE_HEADER_STANDINGS_ENCOUNTER;
        } elseif ( !empty($dungeon) ) {
            $tableFields = self::TABLE_HEADER_STANDINGS_DUNGEON;
        } elseif ( !empty($tier) ) {
            $tableFields = self::TABLE_HEADER_STANDINGS_DUNGEON;
        }

        return $tableFields;
    }

    /**
     * generate model specific internal links
     * 
     * @param  string  $view        [ view type filter ]
     * @param  string  $text        [ display text ]
     * @param  string  $class       [ custom classes ]
     * @param  boolean $spreadsheet [ true if link to spreadsheet popup ]
     * 
     * @return string [ html hyperlink ]
     */
    public function generateInternalHyperLink($view, $text, $class, $spreadsheet = false) {
        $url       = PAGE_STANDINGS . $view;
        $hyperlink = '';

        if ( isset($this->_tier) ) { $url .= '/' . $this->_tier; }
        if ( isset($this->_dungeon) ) { $url .= '/' . $this->_dungeon; }
        if ( isset($this->_encounter) ) { $url .= '/' . $this->_encounter; }
        if ( $spreadsheet ) { $url .= '/spreadsheet'; }

        if (!empty($class)) {
            $class = 'class="' . $class . '"';
        }

        $hyperlink = '<a ' . $class . ' href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}