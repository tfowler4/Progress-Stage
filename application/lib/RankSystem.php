<?php

/**
 * rank system data object
 */
class RankSystem extends DataObject {
    protected $_systemId;
    protected $_identifier;
    protected $_name;
    protected $_abbreviation;
    protected $_baseValue;
    protected $_finalValue;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_systemId     = $params['system_id'];
        $this->_identifier   = $params['identifier'];
        $this->_name         = $params['name'];
        $this->_abbreviation = $params['abbreviation'];
        $this->_baseValue    = $params['base_value'];
        $this->_finalValue   = $params['final_value'];
    }
}