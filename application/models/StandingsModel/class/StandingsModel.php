<?php

/**
 * progression standings guild listing page
 */
class StandingsModel extends Model {
    protected $_standingsArray = array();
    protected $_topGuildsArray = array();
    protected $_header;
    protected $_view;
    protected $_raidSize;
    protected $_tier;
    protected $_tierRaidSize;
    protected $_dungeon;
    protected $_encounter;
    protected $_detailsPane;
    protected $_dataDetails;
    protected $_dataType;
    protected $_identifier;
    protected $_isSpreadsheet;
    protected $_tableHeader;
    protected $_listings;

    const GLOSSARY = array(
            'WF' => 'World Firsts',
            'RF' => 'Region Firsts',
            'SF' => 'Server Firsts',
            'WR' => 'World Rank',
            'RR' => 'Region Rank',
            'SR' => 'Server Rank'
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

        if ( isset($params[0]) ) { $this->_view    = $params[0]; }
        if ( isset($params[1]) ) { $this->_tier    = $params[1]; }
        if ( isset($params[2]) ) { $this->_dungeon = $params[2]; }
        if ( isset($params[3]) ) {
            if ( $params[3] == 'spreadsheet' ) {
                $this->_isSpreadsheet = true;
            } else {
              $this->_encounter = $params[3];
            }
        }

        $this->_listings       = new Listings('standings', $params);
        $this->_standingsArray = $this->_listings->listArray;
        $this->_tableHeader    = $this->_listings->_tableHeader;
        $this->_dataDetails    = $this->_listings->_dataDetails;
        $this->_detailsPane    = $this->_dataDetails;
        $this->_dataDetails->setClears();
        
        $this->title = $this->_listings->_title . ' ' . self::PAGE_TITLE;
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