<?php
class Dungeon {
    protected $_dungeonId;
    protected $_name;
    protected $_abbreviation;
    protected $_tier;
    protected $_tierFullTitle;
    protected $_raidSize;
    protected $_euTimeDiff;
    protected $_euTimeDiffTitle;
    protected $_finalEncounterId;
    protected $_dateLaunch;
    protected $_color;
    protected $_type;
    protected $_numOfEncounters;
    protected $_numOfSpecialEncounters;
    protected $_encounters;
    protected $_firstDungeonClear;
    protected $_recentDungeonClear;
    protected $_numOfDungeonClears;
    protected $_numOfNADungeonClears;
    protected $_numOfEUDungeonClears;

    public function __construct($params) {
        $this->_dungeonId               = $params['dungeon_id'];
        $this->_name                    = $params['name'];
        $this->_abbreviation            = $params['abbreviation'];
        $this->_tier                    = $params['tier'];
        $this->_raidSize                = $params['players'];
        $this->_numOfEncounters         = $params['mobs'];
        $this->_numOfSpecialEncounters  = $params['special_encounters'];
        $this->_finalEncounterId        = $params['final_encounter'];
        $this->_dateLaunch              = Functions::formatDate($params['date_launch'], 'F d Y');
        $this->_euTimeDiff              = $params['eu_diff'];
        $this->_euTimeDiffTitle         = Functions::convertToHoursMins($params['eu_diff']);
        $this->_color                   = $params['color'];
        $this->_type                    = $params['dungeon_type'];
        $this->_encounters              = $this->getEncounters($this->_dungeonId);
        $this->_firstDungeonClear       = 'N/A';
        $this->_recentDungeonClear      = 'N/A';
        $this->_numOfDungeonClears      = 0;
        $this->_numOfNADungeonClears    = 0;
        $this->_numOfEUDungeonClears    = 0;
    }

    public function __get($name) {
        if ( isset($this->$name) ) {
            return $this->$name;
        }
    }
    
    public function __isset($name) {
        return isset($this->$name);
    }

    public function getEncounters($dungeonId) {
        $property = new stdClass();

        foreach( CommonDataContainer::$encounterArray as $encounterId => $encounterDetails ) {
            if ( $encounterDetails->_dungeonId == $dungeonId ) { $property->$encounterId = $encounterDetails; }
        }

        return $property;
    }

    public function setClears() {
        $finalEncounterOrderedArr = array();
        $finalEncounterId         = $this->_finalEncounterId;

        $tierDetails           = CommonDataContainer::$tierArray[$this->_tier];
        $this->_tierFullTitle  = $tierDetails->_name . ' (T' . $tierDetails->_tier . '/' . $tierDetails->_altTier . ')';

        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( isset($guildDetails->_encounterDetails->$finalEncounterId) ) {
                $encounterCompleteTime = $guildDetails->_encounterDetails->$finalEncounterId->_strtotime;
                $finalEncounterOrderedArr[$guildId] = $encounterCompleteTime;

                if ( $guildDetails->_region == 'NA' ) { $this->_numOfNADungeonClears++; }
                if ( $guildDetails->_region == 'EU' ) { $this->_numOfEUDungeonClears++; }

                $this->_numOfDungeonClears++;
            }
        }

        asort($finalEncounterOrderedArr);

        $firstGuild;
        $recentGuild;

        switch ( $this->_numOfDungeonClears ) {
            case 0:
                return;
                break;
            case 1:
                reset($finalEncounterOrderedArr);
                $firstGuild = CommonDataContainer::$guildArray[key($finalEncounterOrderedArr)]->_nameLink;
                $recentGuild = CommonDataContainer::$guildArray[key($finalEncounterOrderedArr)]->_nameLink;
                break;
            default:
                reset($finalEncounterOrderedArr);
                $firstGuild = CommonDataContainer::$guildArray[key($finalEncounterOrderedArr)]->_nameLink;
                arsort($finalEncounterOrderedArr);
                reset($finalEncounterOrderedArr);
                $recentGuild = CommonDataContainer::$guildArray[key($finalEncounterOrderedArr)]->_nameLink;
                break;
        }

        if ( isset($firstGuild) ) { $this->_firstDungeonClear = $firstGuild; }
        if ( isset($recentGuild) ) { $this->_recentDungeonClear = $recentGuild; }
    }

    public function __destruct() {
        
    }
}