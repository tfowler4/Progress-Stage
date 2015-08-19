<?php

/**
 * faction data object
 */
class Faction extends DataObject {
    protected $_factionId;
    protected $_name;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_factionId = $params['faction_id'];
        $this->_name      = $params['name'];
    }
}