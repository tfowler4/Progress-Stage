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

    const TABLE_HEADER_DEFAULT = array(
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

    const PANE_SERVER = array(
            'Server Name'   => '_nameLink',
            'Region'        => '_country',
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

        if ( isset($params[0]) ) { $this->_server   = $params[0]; }
        if ( isset($params[1]) ) { 
            $this->_tier = $params[1]; 
        } else {
            $this->_tier = Functions::cleanLink(CommonDataContainer::$tierArray[LATEST_TIER]->_name);
        }

        if ( isset($params[2]) ) {
            if ( $params[2] == 'spreadsheet' ) {
                $this->_isSpreadsheet = true;
            }
        }

        $this->_tierDetails   = Functions::getTierByName($this->_tier);
        $this->_serverDetails = Functions::getServerByName($this->_server);
        $this->_serverDetails->getGuilds();

        if ( empty($this->_serverDetails) || empty($this->_tierDetails) ) { Functions::sendTo404(); }

        $this->_standingsArray = $this->getStandings($this->_tierDetails);
        $this->_detailsPane    = $this->_serverDetails;
        $this->_tableHeader    = self::TABLE_HEADER_DEFAULT;
        $this->_serverDetails->getFirstEncounterKills();
        
        $this->title = $this->_serverDetails->_name . ' Raid Progression';
    }

    /**
     * get sorted guild array based on number of encounters completed in dungeon
     * 
     * @param  Dungeon $dungeonDetails [ dungeon data object ]
     * 
     * @return array [ sorted guild array by amount completed ]
     */
    public function getTemporarySortArray($dungeonDetails) {
        $sortArray                = array();
        $this->_dungeonGuildArray = array();
        $dungeonId                = $dungeonDetails->_dungeonId;

        foreach ( $this->_serverDetails->_guilds as $guildId => $guildDetails ) {
            $this->_dungeonGuildArray[$guildId] = clone($guildDetails);

            $guildDetails->generateEncounterDetails('dungeon', $dungeonId);

            if ( empty($guildDetails->_dungeonDetails->$dungeonId->_complete) ) { continue; }

            $progressionDetails = $guildDetails->_dungeonDetails->$dungeonId;

            $this->_dungeonGuildArray[$guildId]->mergeViewDetails('_dungeonDetails', $dungeonId);

            $sortArray[$progressionDetails->_complete][$guildId] = $progressionDetails->_recentTime; 
        }

        return $sortArray;
    }

    /**
     * add guild to the temporary guild array
     * 
     * @param GuildDetails &$guildDetails        [ guild details detail object ]
     * @param array        &$temporaryGuildArray [ list of guilds ]
     * @param array        &$completionTimeArray [ list of completion times ]
     * @param integer      &$rankCount           [ guild rank increment ]
     *
     * @return void
     */
    public function addGuildToListArray(&$guildDetails, &$temporaryGuildArray, &$completionTimeArray, &$rankCount) {
        $guildId = $guildDetails->_guildId;

        $guildDetails->getTimeDiff($completionTimeArray, $guildDetails->_strtotime);
        $guildDetails->_rank = $rankCount;

        $temporaryGuildArray[$guildId] = $guildDetails;
        $completionTimeArray           = $guildDetails->_strtotime;
        $rankCount++;
    }

    /**
     * get guild standings based upon tier selected
     * 
     * @param  Tier $tierDetails    [ tier data object ]
     * 
     * @return array [ array of guilds sorted by completion standings ]
     */
    public function getStandings($tierDetails) {
        $returnArray = array();

        foreach ( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
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

                        if ( count($this->_topGuildsArray) < 3 ) { $this->_topGuildsArray[$guildId] = $guildDetails; }

                        if ( !isset($completionTimeArray) ) { $completionTimeArray = 0; }
                        if ( !isset($sortGuildArray) ) { $sortGuildArray = array(); }
                        if ( !isset($rankArray) ) { $rankArray = 1; }

                        $this->addGuildToListArray($guildDetails, $sortGuildArray, $completionTimeArray, $rankArray);
                    }
                }
            }

            $returnArray[$dungeonId] = $this->setViewStandingsArray($sortGuildArray, $dungeonDetails);
        }

        return $returnArray;
    }

    /**
     * set the standings header and guild array per view
     * 
     * @param array   $sortGuildArray [ array of guilds ]
     * @param Dungeon $dungeonDetails [ dungeon data object ]
     *
     * @return object [ standings object array ]
     */
    public function setViewStandingsArray($sortGuildArray, $dungeonDetails) {
        $retVal         = new stdClass();
        $retVal->header = $dungeonDetails->_name . ' Standings';
        $retVal->data   = (!empty($sortGuildArray) ? $sortGuildArray : array());

        return $retVal;
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