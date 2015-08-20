<?php

/**
 * rank view details detail object
 */
class RankViewDetails extends DetailObject {
    protected $_world;
    protected $_region;
    protected $_server;
    protected $_country;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_world   = $params[0];
        $this->_server  = $params[1];
        $this->_region  = $params[2];
        $this->_country = $params[3];
    }
}