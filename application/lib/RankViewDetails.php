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
    public function __construct($worldRank, $regionRank, $serverRank, $countryRank) {
        $this->_world   = $worldRank;
        $this->_server  = $regionRank;
        $this->_region  = $serverRank;
        $this->_country = $countryRank;
    }
}