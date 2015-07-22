<?php
class RaidSize {
    protected $_raidSize;
    protected $_numOfDungeons;
    protected $_numOfEncounters;
    protected $_dungeons;
    protected $_encounters;
    protected $_abbreviation;

    public function RaidSize($raidSize) {
        $this->_raidSize        = $raidSize;
        $this->_numOfDungeons   = 0;
        $this->_numOfEncounters = 0;
        $this->_dungeons        = $this->getDungeons();
        $this->_encounters      = $this->getEncounters();
        $this->_abbreviation    = $raidSize . 'M';
    }

    public function __get($name) {
        return $this->$name;
    }

    public function __set($name, $value) {
        $this->$name = $name;
    }
    
    public function __isset($name) {
        return isset($this->$name);
    }

    public function getDungeons() {
        $property = new stdClass();

        foreach( CommonDataContainer::$dungeonArray as $dungeon_id => $dungeon_details ) {
            if ( $dungeon_details->_raidSize == $this->_raidSize ) { $property->$dungeon_id = $dungeon_details; $this->_numOfDungeons++; }
        }

        return $property;
    }

    public function getEncounters() {
        $property = new stdClass();

        foreach( CommonDataContainer::$encounterArray as $encounter_id => $encounter_details ) {
            if ( $encounter_details->_raidSize == $this->_raidSize ) { $property->$encounter_id = $encounter_details; $this->_numOfEncounters++; }
        }

        return $property;
    }

    public function __destruct() {
        
    }
}