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
    
    protected $_detailsPane;
    protected $_dataDetails;
    protected $_guildListing;

    const GLOSSARY = array(
            'WF' => 'World Firsts',
            'RF' => 'Region Firsts',
            'SF' => 'Server Firsts',
            'WR' => 'World Rank',
            'RR' => 'Region Rank',
            'SR' => 'Server Rank'
        );

    const TABLE_HEADER_STANDINGS_DUNGEON = array(
            'Rank'            => '_rank',
            'Guild'           => '_nameLink',
            'Server'          => '_serverLink',
            'Progress'        => '_standing',
            'Hard Modes'      => '_hardModeStanding',
            'Conqueror'       => '_conqeuror',
            'WF'              => '_worldFirst',
            'RF'              => '_regionFirst',
            'SF'              => '_serverFirst',
            'Recent Activity' => '_recentActivity'
        );

    const TABLE_HEADER_STANDINGS_ENCOUNTER = array(
            'Rank'            => '_rank',
            'Guild'           => '_nameLink',
            'Server'          => '_serverLink',
            'Date Completed'  => '_datetime',
            'Time Difference' => '_timeDiff',
            'Server Achieved' => '_killServer',
            'WR'              => '_worldRankImage',
            'RR'              => '_regionRankImage',
            'SR'              => '_serverRankImage',
            'Kill Video'      => '_videoLink',
            'Screenshot'      => '_screenshotLink'
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

    const PANE_ENCOUNTER = array(
            'Name'             => '_name',
            'Tier'             => '_tierFullTitle',
            'Dungeon'          => '_dungeon',
            'Raid Size'        => '_raidSize',
            'WW Clears'        => '_numOfEncounterKills',
            'NA Clears'        => '_numOfNAEncounterKills',
            'EU Clears'        => '_numOfEUEncounterKills',
            'First Kill'       => '_firstEncounterKill',
            'Most Recent Kill' => '_recentEncounterKill'
        );

    const PAGE_TITLE = 'Progression Standings';
    const PAGE_NAME  = 'Standings';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_guildListing = new Listings('standings', $params);

        if ( isset($params[0]) ) { $this->_view    = $params[0]; }
        if ( isset($params[1]) ) { $this->_tier    = $params[1]; }
        if ( isset($params[2]) ) { $this->_dungeon = $params[2]; }
        if ( isset($params[3]) ) { $this->_encounter = $params[3]; }

        switch($this->_view) {
            case 'world':
                if ( isset($this->_guildListing->listArray->world['world']) ) {
                    $this->_standingsArray['world'] = $this->_guildListing->listArray->world['world'];
                    $this->_standingsArray['world']->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                    $this->_standingsArray['world']->headerText  = 'World Standings';
                }
                break;
            case 'region':
                foreach( CommonDataContainer::$regionArray as $regionId => $regionDetails ) {
                    $name         = $regionDetails->_name;
                    $abbreviation = $regionDetails->_abbreviation;
                    $style        = $regionDetails->_style;

                    if ( isset($this->_guildListing->listArray->region[$abbreviation]) ) {
                        $this->_standingsArray[$abbreviation] = $this->_guildListing->listArray->region[$abbreviation];
                        $this->_standingsArray[$abbreviation]->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                        $this->_standingsArray[$abbreviation]->headerText  = $style . ' Standings';
                    }
                }
                break;
            case 'server':
                foreach ( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
                    $server = $serverDetails->_name;
                    $region = $serverDetails->_region;

                    if ( isset($this->_guildListing->listArray->server[$server]) ) {
                        $this->_standingsArray[$server] = $this->_guildListing->listArray->server[$server];
                        $this->_standingsArray[$server]->tableFields = $this->_setTableFields($this->_tier, $this->_dungeon, $this->_encounter);
                        $this->_standingsArray[$server]->headerText  = $server . ' Standings';
                    }
                }
                break;
        }

        $this->_dataDetails    = $this->_guildListing->_dataDetails;
        $this->_detailsPane    = $this->_dataDetails;
        $this->_topGuildsArray = $this->_guildListing->_topGuildsArray;
        $this->_dataDetails->setClears();

        $this->title = $this->_dataDetails->_name . ' ' . ucfirst($this->_view) . ' ' . self::PAGE_TITLE;
    }

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
     * @param  boolean $spreadsheet [ true if link to spreadsheet popup ]
     * 
     * @return string [ html hyperlink ]
     */
    public function generateInternalHyperLink($view, $text, $spreadsheet = false) {
        $url       = PAGE_STANDINGS . $view;
        $hyperlink = '';

        if ( isset($this->_tier) ) { $url .= '/' . $this->_tier; }
        if ( isset($this->_dungeon) ) { $url .= '/' . $this->_dungeon; }
        if ( isset($this->_encounter) ) { $url .= '/' . $this->_encounter; }
        if ( $spreadsheet ) { $url .= '/spreadsheet'; }

        $hyperlink = '<a href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}