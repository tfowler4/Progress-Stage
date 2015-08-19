<?php

/**
 * encounter data object
 */
class Encounter extends DataObject {
    protected $_encounterId;
    protected $_name;
    protected $_dungeon;
    protected $_dungeonId;
    protected $_raidSize;
    protected $_tier;
    protected $_tierFullTitle;
    protected $_type;
    protected $_encounterName;
    protected $_encounterShortName;
    protected $_dateLaunch;
    protected $_encounterOrder;
    protected $_reqEncounter;
    protected $_firstEncounterKill;
    protected $_recentEncounterKill;
    protected $_numOfEncounterKills;
    protected $_numOfNAEncounterKills;
    protected $_numOfEUEncounterKills;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_encounterId           = $params['encounter_id'];
        $this->_name                  = $params['name'];
        $this->_dungeon               = $params['dungeon'];
        $this->_dungeonId             = $params['dungeon_id'];
        $this->_raidSize              = $params['players'];
        $this->_tier                  = $params['tier'];
        $this->_type                  = $params['mob_type'];
        $this->_encounterName         = $params['encounter_name'];
        $this->_encounterShortName    = $params['encounter_short_name'];
        $this->_dateLaunch            = $params['date_launch'];
        $this->_encounterOrder        = $params['mob_order'];
        $this->_reqEncounter          = $params['req_encounter'];
        $this->_firstEncounterKill    = 'N/A';
        $this->_recentEncounterKill   = 'N/A';
        $this->_numOfEncounterKills   = 0;
        $this->_numOfNAEncounterKills = 0;
        $this->_numOfEUEncounterKills = 0;
    }

    /**
     * set encounter clear information for guilds
     * 
     * @return void
     */
    public function setClears() {
        $encounterKillOrderedArray = array();
        $encounterId               = $this->_encounterId;

        $tierDetails           = CommonDataContainer::$tierArray[$this->_tier];
        $this->_tierFullTitle  = $tierDetails->_name . ' (T' . $tierDetails->_tier . '/' . $tierDetails->_altTier . ')';

        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( isset($guildDetails->_encounterDetails->$encounterId) ) {
                $encounterCompleteTime = $guildDetails->_encounterDetails->$encounterId->_strtotime;
                $encounterKillOrderedArray[$guildId] = $encounterCompleteTime;

                if ( $guildDetails->_region == 'NA' ) { $this->_numOfNAEncounterKills++; }
                if ( $guildDetails->_region == 'EU' ) { $this->_numOfEUEncounterKills++; }
                $this->_numOfEncounterKills++;
            }
        }

        asort($encounterKillOrderedArray);

        $firstGuild;
        $recentGuild;

        switch ( $this->_numOfEncounterKills ) {
            case 0:
                return;
                break;
            case 1:
                reset($encounterKillOrderedArray);
                $firstGuild  = CommonDataContainer::$guildArray[key($encounterKillOrderedArray)]->_nameLink;
                $recentGuild = CommonDataContainer::$guildArray[key($encounterKillOrderedArray)]->_nameLink;
                break;
            default:
                reset($encounterKillOrderedArray);
                $firstGuild = CommonDataContainer::$guildArray[key($encounterKillOrderedArray)]->_nameLink;
                arsort($encounterKillOrderedArray);
                reset($encounterKillOrderedArray);
                $recentGuild = CommonDataContainer::$guildArray[key($encounterKillOrderedArray)]->_nameLink;
                break;
        }

        if ( isset($firstGuild) ) { $this->_firstEncounterKill = $firstGuild; }
        if ( isset($recentGuild) ) { $this->_recentEncounterKill = $recentGuild; }
    }
}