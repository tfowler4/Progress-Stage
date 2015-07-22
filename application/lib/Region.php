<?php
class Region {
    protected $_regionId;
    protected $_abbreviation;
    protected $_full;
    protected $_style;
    protected $_numOfServers = 0;
    protected $_regionImage;

    public function Region($params) {
        $this->_regionId        = $params['region_id'];
        $this->_abbreviation    = $params['abbreviation'];
        $this->_name            = $params['full'];
        $this->_style           = $params['style'];
        $this->_numOfServers    = $params['num_of_servers'];
        $this->_regionImage     = Functions::getImageFlag($this->_abbreviation, '');
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __isset($name) {
        return isset($this->$name);
    }

    public function __destruct() {
        
    }
}