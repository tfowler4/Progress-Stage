<?php
class Faction {
    protected $_factionId;
    protected $_name;

    public function Faction($params) {
        $this->_factionId = $params['faction_id'];
        $this->_name      = $params['name'];
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