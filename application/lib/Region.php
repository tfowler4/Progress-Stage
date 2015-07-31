<?php
class Region {
    protected $_regionId;
    protected $_abbreviation;
    protected $_full;
    protected $_style;
    protected $_numOfServers = 0;
    protected $_regionImage;
    protected $_servers;

    public function Region($params) {
        $this->_regionId        = $params['region_id'];
        $this->_abbreviation    = $params['abbreviation'];
        $this->_name            = $params['full'];
        $this->_style           = $params['style'];
        $this->_numOfServers    = $params['num_of_servers'];
        $this->_regionImage     = Functions::getImageFlag($this->_abbreviation, '');
        $this->_servers         = $this->getServers($this->_abbreviation);
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __isset($name) {
        return isset($this->$name);
    }

    public function getServers($region) {
        $property = new stdClass();

        foreach( CommonDataContainer::$serverArray as $serverId => $serverDetails ) {
            if ( $serverDetails->_region == $region ) { $property->$serverId = $serverDetails; }
        }

        return $property;
    }

    public function __destruct() {
        
    }
}