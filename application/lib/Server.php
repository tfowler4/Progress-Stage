<?php
class Server {
    protected $_serverId;
    protected $_name;
    protected $_nameLink;
    protected $_country;
    protected $_countryImage;
    protected $_region;
    protected $_type;
    protected $_type2;
    protected $_numOfGuilds = 0;
    protected $_numOfRegionFirsts = 0;
    protected $_numOfWorldFirsts = 0;
    protected $_guilds;

    public function Server($params) {
        $this->_serverId        = $params['server_id'];
        $this->_name            = $params['name'];
        $this->_country         = $params['country'];
        $this->_region          = $params['region'];
        $this->_type            = $params['type'];
        $this->_type2           = $params['type2'];
        $this->_countryImage    = Functions::getImageFlag($this->_country, '');
        $this->_nameLink        = $this->_countryImage . '<span style="vertical-align:middle;">' . Functions::generateInternalHyperLink('servers', '', '', $this->_name, '') . '</span>';
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __set($name, $value) {
        $this->$name = $value;
    }
    
    public function __isset($name) {
        return isset($this->$name);
    }

    public function getGuilds() {
        $guilds = new stdClass();

        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( $guildDetails->_server == $this->_name ) {
                $guilds->$guildId = $guildDetails;
                $this->_numOfGuilds++; 
            }
        }

        $this->_guilds = $guilds;
    }

    public function getFirstEncounterKills() {
        foreach( $this->_guilds as $guildId => $guildDetails ) {
            $this->_numOfRegionFirsts += $guildDetails->_regionFirst;
            $this->_numOfWorldFirsts  += $guildDetails->_worldFirst;
        }
    }

    public function __destruct() {}
}