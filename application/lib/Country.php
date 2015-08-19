<?php

/**
 * country data object
 */
class Country extends DataObject {
    protected $_countryId;
    protected $_name;
    protected $_region;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_countryId = $params['country_id'];
        $this->_name      = $params['name'];
        $this->_region    = $params['region'];
    }
}