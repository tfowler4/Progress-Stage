<?php

/**
 * specific server progression standings page
 */
class ServersModel extends Model {
    protected $_standingsArray = array();
    protected $_topGuildsArray = array();

    protected $_server;
    protected $_tier;
    protected $_dungeon;

    protected $_detailsPane;
    protected $_tierDetails;
    protected $_serverDetails;
    protected $_guildListing;

    const TABLE_HEADER_STANDINGS_DUNGEON = array(
            'Rank'            => '_rank',
            'Guild'           => '_nameLink',
            'Server'          => '_serverLink',
            'Progress'        => '_progress',
            'Hard Modes'      => '_specialProgress',
            'Conqueror'       => '_achievement',
            'WF'              => '_worldFirst',
            'RF'              => '_regionFirst',
            'SF'              => '_serverFirst',
            'Recent Activity' => '_recentActivity'
        );

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

        if ( isset($params[0]) ) { $this->_server = $params[0]; }
        if ( isset($params[1]) ) { 
            $this->_tier = $params[1];
        } else {
            $this->_tier = Functions::cleanLink(CommonDataContainer::$tierArray[LATEST_TIER]->_name);
        }

        $this->_serverDetails = Functions::getServerByName($this->_server);
        $this->_tierDetails   = Functions::getTierByName($this->_tier);
        $this->_detailsPane = $this->_serverDetails;

        $server = $this->_serverDetails->_name;

        foreach( $this->_tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
            $dungeonName = Functions::cleanLink($dungeonDetails->_name);
            $params[2] = $dungeonName;

            $this->_guildListing = new Listings('servers', $params, $this->_serverDetails);
            $this->_standingsArray[$dungeonId] = $this->_guildListing->listArray->server[$server];
            $this->_standingsArray[$dungeonId]->tableFields = $this->_setTableFields();
            $this->_standingsArray[$dungeonId]->headerText  = $dungeonDetails->_name . ' Standings';

            if ( empty($this->_topGuildsArray) ) {
                $this->_topGuildsArray = $this->_guildListing->_topGuildsArray;
            }
        }

        if ( isset($this->_guildListing) ) {
            $this->_dataDetails = $this->_guildListing->_dataDetails;
            $this->_detailsPane = $this->_guildListing->_serverDetails;
            $this->_detailsPane->getFirstEncounterKills($this->_standingsArray);

            $this->title = $this->_detailsPane->_name . ' ' . self::PAGE_TITLE;
        }
    }

    /**
     * set the data table header fields to be displayed
     *
     * @return void
     */
    private function _setTableFields() {
        $tableFields = self::TABLE_HEADER_STANDINGS_DUNGEON;

        return $tableFields;
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
        $url       = PAGE_SERVERS . $this->_server;
        $hyperlink = '';

        if ( isset($tier) ) { $url .= '/' . Functions::cleanLink($tier); }
        if ( $spreadsheet ) { $url .= '/spreadsheet'; }

        $hyperlink = '<a href="' . $url . '" target"_blank">' . $text . '</a>';

        return $hyperlink;
    }
}