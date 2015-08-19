<?php

/**
 * tier raid size data object
 */
class TierSize extends DataObject {
    protected $_tier;
    protected $_raidSize;
    protected $_tierSize;
    protected $_numOfDungeons;
    protected $_numOfEncounters;
    protected $_numOfSpecialEncounters;
    protected $_dungeons;
    protected $_encounters;
    protected $_abbreviation;

    public function __construct($tierDetails, $raidSize, $tierSize) {
        $this->_tier                    = $tierDetails->_tier;
        $this->_raidSize                = $raidSize;
        $this->_tierSize                = $tierSize;
        $this->_numOfDungeons           = 0;
        $this->_numOfEncounters         = 0;
        $this->_numOfSpecialEncounters  = 0;
        $this->_dungeons                = $this->getDungeons();
        $this->_encounters              = $this->getEncounters();
        $this->_abbreviation            = 'T' . $tierDetails->_tier . ' ' . $raidSize . 'M';
    }

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