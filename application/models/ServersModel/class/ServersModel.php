<?php

/**
 * specific server progression standings page
 */
class ServersModel extends Model {
    protected $_dungeonGuildArray;
    protected $_standingsArray;
    protected $_dungeonArray;
    protected $_tierDetails;
    protected $_topGuildsArray;
    protected $_serverDetails;
    protected $_server;
    protected $_tier;
    protected $_dungeon;
    protected $_detailsPane;
    protected $_isSpreadsheet;
    protected $_tableHeader;
    protected $_listings;

    const PANE_SERVER = array(
            'Server Name'   => '_nameLink',
            'Region'        => '_region',
            'Guilds'        => '_numOfGuilds',
            'Region Firsts' => '_numOfRegionFirsts',
            'World Firsts'  => '_numOfWorldFirsts'
        );

    const GLOSSARY = array(
            'WF' => 'World Firsts',
            'RF' => 'Region Firsts',
            'SF' => 'Server Firsts',
            'WR' => 'World Rank',
            'RR' => 'Region Rank',
            'SR' => 'Server Rank'
        );

    const PAGE_TITLE = 'Server Progression';
    const PAGE_NAME  = '';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_listings       = new Listings('servers', $params);
        $this->_standingsArray = $this->_listings->listArray;
        $this->_tableHeader    = $this->_listings->_tableHeader;
        $this->_dataDetails    = $this->_listings->_dataDetails;
        $this->_detailsPane    = $this->_listings->_serverDetails;
        $this->_detailsPane->getFirstEncounterKills();

        $this->title = $this->_detailsPane->_name . ' Raid Progression';
    }

    /**
     * generate model specific internal links
     * 
     * @param  string  $tier        [ tier name ]
     * @param  string  $text        [ display text ]
     * @param  boolean $spreadsheet [ true if link to spreadsheet popup ]
     * 
     * @return string [ html hyperlink ]
     */
    public function generateInternalHyperLink($tier, $text, $spreadsheet = false) {
        $url       = PAGE_SERVERS . $this->_listings->_server;
        $hyperlink = '';

        if ( isset($tier) ) { $url .= '/' . Functions::cleanLink($tier); }
        if ( $spreadsheet ) { $url .= '/spreadsheet'; }

        $hyperlink = '<a href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}