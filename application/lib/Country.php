<?php
class Country {
    protected $_country_id;
    protected $_name;
    protected $_region;

    public function Country($params) {
        $this->_country_id  = $params['country_id'];
        $this->_name        = $params['name'];
        $this->_region      = $params['region'];
    }

    public function __get($name) {
        if ( isset($this->$name) ) {
            return $this->$name;
        }
    }
    
    public function __isset($name) {
        return isset($this->$name);
    }

    public function __destruct() {
        
    }
}