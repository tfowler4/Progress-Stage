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
    protected $_listings;

    const LIMIT_TREND_UNRANK = 10;
    const LIMIT_TREND_RANK   = 10;
    const LIMIT_TREND        = 10;
    const LIMIT_TREND_TOTAL  = 15;

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

        $this->_listings      = new Listings('rankings', $params);
        $this->_rankingsArray = $this->_listings->listArray;
        $this->_tableHeader   = $this->_listings->_tableHeader;
        $this->_dataDetails   = $this->_listings->_dataDetails;
        $this->_detailsPane   = $this->_dataDetails;
        $this->_dataDetails->setClears();

        $this->title = $this->_listings->_title . ' ' . self::PAGE_TITLE;
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
        if ( isset($this->_listings->_tier) ) { $url    .= '/' . $this->_listings->_tier; }
        if ( isset($this->_listings->_dungeon) ) { $url .= '/' . $this->_listings->_dungeon; }

        $hyperlink = '<a href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}