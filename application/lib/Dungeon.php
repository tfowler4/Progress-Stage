<?php

/**
 * dungeon data object
 */
class Dungeon extends DataObject {
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
    protected $_type;
    protected $_numOfEncounters;
    protected $_numOfSpecialEncounters;
    protected $_encounters;
    protected $_firstDungeonClear;
    protected $_recentDungeonClear;
    protected $_numOfDungeonClears;
    protected $_numOfNADungeonClears;
    protected $_numOfEUDungeonClears;

    /**
     * constructor
     * 
     * @param array $params [ PDO query object ]
     */
    public function __construct($params) {
        $this->_dungeonId              = $params['dungeon_id'];
        $this->_name                   = $params['name'];
        $this->_abbreviation           = $params['abbreviation'];
        $this->_tier                   = $params['tier'];
        $this->_raidSize               = $params['players'];
        $this->_numOfEncounters        = $params['mobs'];
        $this->_numOfSpecialEncounters = $params['special_encounters'];
        $this->_finalEncounterId       = $params['final_encounter'];
        $this->_dateLaunch             = Functions::formatDate($params['date_launch'], 'F d Y');
        $this->_euTimeDiff             = $params['eu_diff'];
        $this->_euTimeDiffTitle        = Functions::convertToHoursMins($params['eu_diff']);
        $this->_type                   = $params['dungeon_type'];
        $this->_encounters             = $this->_getEncounters($this->_dungeonId);
        $this->_firstDungeonClear      = 'N/A';
        $this->_recentDungeonClear     = 'N/A';
        $this->_numOfDungeonClears     = 0;
        $this->_numOfNADungeonClears   = 0;
        $this->_numOfEUDungeonClears   = 0;
    }

    /**
     * get all encounters within a dungeon
     * 
     * @param  string $dungeonId [ dungeon id ]
     * 
     * @return object [ property containing all encounters from dungeon ]
     */
    private function _getEncounters($dungeonId) {
        $property = new stdClass();

        foreach( CommonDataContainer::$encounterArray as $encounterId => $encounterDetails ) {
            if ( $encounterDetails->_dungeonId == $dungeonId ) { $property->$encounterId = $encounterDetails; }
        }

        return $property;
    }

    /**
     * set dungeon clear information for guilds
     * 
     * @return void
     */
    public function setClears() {
        $finalEncounterOrderedArray = array();
        $finalEncounterId           = $this->_finalEncounterId;

        $tierDetails          = CommonDataContainer::$tierArray[$this->_tier];
        $this->_tierFullTitle = $tierDetails->_name . ' (T' . $tierDetails->_tier . '/' . $tierDetails->_altTier . ')';

        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( isset($guildDetails->_encounterDetails->$finalEncounterId) ) {
                $encounterCompleteTime                = $guildDetails->_encounterDetails->$finalEncounterId->_strtotime;
                $finalEncounterOrderedArray[$guildId] = $encounterCompleteTime;

                if ( $guildDetails->_region == 'NA' ) { $this->_numOfNADungeonClears++; }
                if ( $guildDetails->_region == 'EU' ) { $this->_numOfEUDungeonClears++; }

                $this->_numOfDungeonClears++;
            }
        }

        asort($finalEncounterOrderedArray);

        $firstGuild;
        $recentGuild;

        switch ( $this->_numOfDungeonClears ) {
            case 0:
                return;
                break;
            case 1:
                reset($finalEncounterOrderedArray);
                $firstGuild  = CommonDataContainer::$guildArray[key($finalEncounterOrderedArray)]->_nameLink;
                $recentGuild = CommonDataContainer::$guildArray[key($finalEncounterOrderedArray)]->_nameLink;
                break;
            default:
                reset($finalEncounterOrderedArray);
                $firstGuild = CommonDataContainer::$guildArray[key($finalEncounterOrderedArray)]->_nameLink;
                arsort($finalEncounterOrderedArray);
                reset($finalEncounterOrderedArray);
                $recentGuild = CommonDataContainer::$guildArray[key($finalEncounterOrderedArray)]->_nameLink;
                break;
        }

        if ( isset($firstGuild) ) { $this->_firstDungeonClear = $firstGuild; }
        if ( isset($recentGuild) ) { $this->_recentDungeonClear = $recentGuild; }
    }
}