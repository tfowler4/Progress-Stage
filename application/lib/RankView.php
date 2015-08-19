<?php
class RankView {
    protected $_world;
    protected $_region;
    protected $_server;
    protected $_country;

    public function __construct($params) {
        $this->_world   = $params[0];
        $this->_server  = $params[1];
        $this->_region  = $params[2];
        $this->_country = $params[3];
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