<?php
class TierSizeDetails {
    protected $_complete = 0;
    protected $_standing;
    protected $_hardModeComplete = 0;
    protected $_hardModeStanding;
    protected $_recentActivity = '';
    protected $_recentTime = '';
    protected $_recentEncounterDetails = '';
    protected $_worldFirst = 0;
    protected $_regionFirst = 0;
    protected $_serverFirst = 0;

    public function __construct(&$tierSizeDetails) {
        $this->_standing         = 0 . '/' . $tierSizeDetails->_numOfEncounters;
        $this->_hardModeStanding = 0 . '/' . $tierSizeDetails->_numOfSpecialEncounters;
    }

    public function __get($name) {
        if ( isset($this->$name) ) {
            return $this->$name;
        }
    }
    
    public function __set($name, $value) {
        $this->$name = $value;
    }

    public function __isset($name) {
        return isset($this->$name);
    }

    public function getProperties() {
        return get_object_vars($this);
    }

    public function __destruct() {
        
    }

    public function __unset($name) {
        unset($this->$name);
    }
}