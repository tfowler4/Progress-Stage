<?php

/**
 * tier raid size data object
 */
class TierRaidSize extends DataObject {
    protected $_tier;
    protected $_raidSize;
    protected $_tierRaidSize;
    protected $_numOfDungeons;
    protected $_numOfEncounters;
    protected $_numOfSpecialEncounters;
    protected $_dungeons;
    protected $_encounters;
    protected $_abbreviation;

    /**
     * constructor
     */
    public function __construct($tierDetails, $raidSize, $tierRaidSize) {
        $this->_tier                    = $tierDetails->_tier;
        $this->_raidSize                = $raidSize;
        $this->_tierRaidSize            = $tierRaidSize;
        $this->_numOfDungeons           = 0;
        $this->_numOfEncounters         = 0;
        $this->_numOfSpecialEncounters  = 0;
        $this->_dungeons                = $this->getDungeons();
        $this->_encounters              = $this->getEncounters();
        $this->_abbreviation            = 'T' . $tierDetails->_tier . ' ' . $raidSize . 'M';
    }

    /**
     * get all dungeons with a specific tier raid size
     * 
     * @return object [ property containing all dungeons from a tier raid size ]
     */
    public function getDungeons() {
        $property = new stdClass();

        foreach( CommonDataContainer::$dungeonArray as $dungeonId => $dungeonDetails ) {
            if ( $dungeonDetails->_raidSize == $this->_raidSize && $dungeonDetails->_tier == $this->_tier ) {
                $property->$dungeonId = $dungeonDetails;
                $this->_numOfDungeons++;
            }
        }

        return $property;
    }

    /**
     * get all encounters with a specific tier
     * 
     * @return object [ property containing all raid sizes from a tier raid size ]
     */
    public function getEncounters() {
        $property = new stdClass();

        foreach( CommonDataContainer::$encounterArray as $encounterId => $encounterDetails ) {
            if ( $encounterDetails->_raidSize == $this->_raidSize && $encounterDetails->_tier == $this->_tier ) {
                $property->$encounterId = $encounterDetails;
                $this->_numOfEncounters++;

                if ( $encounterDetails->_type == 2 ) { $this->_numOfSpecialEncounters++; }
            }
        }

        return $property;
    }
}