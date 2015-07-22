<?php
class RankSystem {
    protected $_systemId;
    protected $_identifier;
    protected $_name;
    protected $_abbreviation;
    protected $_baseValue;
    protected $_finalValue;

    public function RankSystem($params) {
        $this->_systemId        = $params['system_id'];
        $this->_identifier      = $params['identifier'];
        $this->_name            = $params['name'];
        $this->_abbreviation    = $params['abbreviation'];
        $this->_baseValue       = $params['base_value'];
        $this->_finalValue      = $params['final_value'];
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