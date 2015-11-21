<?php

/**
 * rank details detail object
 */
class RankDetails extends DetailObject {
    protected $_id;
    protected $_system;
    protected $_points;
    protected $_rank;
    protected $_prevRank;
    protected $_trend;

    /**
     * constructor
     */
    public function __construct($system, $params, $id) {
        $this->_system = strtolower($system);
        $this->_id     = $id;

        if ( !empty($params[$this->_system . '_points']) ) {
            $this->_points = $params[$this->_system . '_points'];
        }

        if ( isset($params[$this->_system . '_world_rank']) ) {
            $this->_rank = new RankViewDetails(
                $params[$this->_system . '_world_rank'],
                $params[$this->_system . '_region_rank'],
                $params[$this->_system . '_server_rank'],
                $params[$this->_system . '_country_rank']
            );
        }

        if ( isset($params[$this->_system . '_world_trend']) ) {
            $this->_trend = new RankViewDetails(
                $params[$this->_system . '_world_trend'],
                $params[$this->_system . '_region_trend'],
                $params[$this->_system . '_server_trend'],
                $params[$this->_system . '_country_trend']
            );
        }

        if ( isset($params[$this->_system . '_world_prev_rank']) ) {
            $this->_prevRank = new RankViewDetails(
                $params[$this->_system . '_world_prev_rank'],
                $params[$this->_system . '_region_prev_rank'],
                $params[$this->_system . '_server_prev_rank'],
                $params[$this->_system . '_country_prev_rank']
            );
        }
    }
}