<?php

/**
 * server data object
 */
class Server extends DataObject {
    protected $_serverId;
    protected $_name;
    protected $_nameLink;
    protected $_navLink;
    protected $_country;
    protected $_countryImage;
    protected $_region;
    protected $_type;
    protected $_type2;
    protected $_numOfGuilds = 0;
    protected $_numOfRegionFirsts = 0;
    protected $_numOfWorldFirsts = 0;
    protected $_guilds;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_serverId     = $params['server_id'];
        $this->_name         = $params['name'];
        $this->_country      = $params['country'];
        $this->_region       = $params['region'];
        $this->_type         = $params['type'];
        $this->_type2        = $params['type2'];
        $this->_countryImage = Functions::getImageFlag($this->_country, '');
        $this->_nameLink     = $this->_countryImage . '<span>' . Functions::generateInternalHyperLink('servers', '', '', $this->_name, '') . '</span>';
        $this->_navLink      = Functions::generateInternalHyperLink('servers', '', '', $this->_name, '');
    }

    /**
     * get all guilds on a specific server
     * 
     * @return void
     */
    public function getGuilds() {
        $guilds = array();

        if ( !empty($this->_guilds) ) {
            $guilds = $this->_guilds;
        }

        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( !isset($this->_guilds[$guildId]) && $guildDetails->_server == $this->_name ) {
                $guilds[$guildId] = $guildDetails;
                $this->_numOfGuilds++; 
            }
        }

        $this->_guilds = $guilds;
    }
   
    /**
     * get all region/world first kills of server
     *
     * @param $guildArray [ server guilds array ]
     * 
     * @return void
     */
    public function getFirstEncounterKills($guildArray) {
        foreach( $guildArray as $dungeonId => $dungeonDetails ) {
            foreach ( $dungeonDetails->data as $guildId => $guildDetails ) {
                $this->_numOfRegionFirsts += $guildDetails->_regionFirst;
                $this->_numOfWorldFirsts  += $guildDetails->_worldFirst;
            }
        }
    }
}