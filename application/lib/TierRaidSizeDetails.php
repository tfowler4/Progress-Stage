<?php

/**
 * tier raid size details detail object
 */
class TierRaidSizeDetails extends DetailObject {
    protected $_complete = 0;
    protected $_standing;
    protected $_hardModeComplete = 0;
    protected $_hardModeStanding;
    protected $_recentActivity;
    protected $_recentTime;
    protected $_recentEncounterDetails;
    protected $_worldFirst = 0;
    protected $_regionFirst = 0;
    protected $_serverFirst = 0;

    /**
     * constructor
     */
    public function __construct(&$tierRaidSizeDetails) {
        $this->_standing         = 0 . '/' . $tierRaidSizeDetails->_numOfEncounters;
        $this->_hardModeStanding = 0 . '/' . $tierRaidSizeDetails->_numOfSpecialEncounters;
    }
}