<?php
class RankDetails {
    protected $_id;
    protected $_system;
    protected $_points;
    protected $_rank;
    protected $_prevRank;
    protected $_trend;

    public function RankDetails($params, $id) {
        $this->_system = $params[0];
        $this->_id     = $id;
        $this->_points = !empty($params[1]) ? $params[1] : 0;

        if ( !empty($params[2]) ) { $this->_rank = new RankView(array_slice($params, 2, 4)); }
        if ( !empty($params[6]) ) { $this->_trend = new RankView(array_slice($params, 6, 4)); }
        if ( !empty($params[10]) ) { $this->_prevRank = new RankView(array_slice($params, 10, 4)); }
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __isset($name) {
        return isset($this->$name);
    }

    public function getProperties() {
        return get_object_vars($this);
    }

    public function __destruct() {
        
    }
}