<?php

/**
 * tier data object
 */
class Tier extends DataObject {
    protected $_tierId;
    protected $_tier;
    protected $_altTier;
    protected $_dateStart;
    protected $_dateEnd;
    protected $_name;
    protected $_altTitle;
    protected $_tierFullNum;
    protected $_specialEncounters;
    protected $_numOfDungeons;
    protected $_numOfEncounters;
    protected $_dungeons;
    protected $_encounters;
    protected $_raidSizes;
    protected $_abbreviation;
    protected $_firstTierClear;
    protected $_recentTierClear;
    protected $_numOfTierClears;
    protected $_numOfNATierClears;
    protected $_numOfEUTierClears;

    /**
     * constructor
     */
    public function __construct(&$params) {
        $this->_tierId                  = $params['tier_id'];
        $this->_tier                    = $params['tier'];
        $this->_altTier                 = $params['alt_tier'];
        $this->_dateStart               = Functions::formatDate($params['date_start'], 'F d Y');
        $this->_name                    = $params['title'];
        $this->_altTitle                = $params['alt_title'];
        $this->_numOfEncounters         = $params['encounters'];
        $this->_numOfSpecialEncounters  = $params['special_encounters'];
        $this->_abbreviation            = $this->_altTier;
        $this->_numOfDungeons           = 0;
        $this->_numOfEncounters         = 0;
        $this->_dungeons                = $this->_getDungeons($this->_tier);
        $this->_encounters              = $this->_getEncounters($this->_tier);
        $this->_raidSizes               = $this->_getRaidSizes();
        $this->_firstTierClear          = 'N/A';
        $this->_recentTierClear         = 'N/A';
        $this->_numOfTierClears         = 0;
        $this->_numOfNATierClears       = 0;
        $this->_numOfEUTierClears       = 0;

        if ( $params['date_end'] == '0000-00-00' ) {
            $this->_dateEnd = 'Currently Active';
        } else {
            $this->_dateEnd = Functions::formatDate($params['date_end'], 'F d Y');
        }
    }

    /**
     * get all dungeons with a specific tier
     *
     * @param  string $tier [ tier of content ]
     * 
     * @return object [ property containing all dungeons from a tier ]
     */
    private function _getDungeons($tier) {
        $property = new stdClass();

        krsort(CommonDataContainer::$dungeonArray);
        foreach( CommonDataContainer::$dungeonArray as $dungeonId => $dungeonDetails ) {
            if ( $dungeonDetails->_tier == $tier ) { $property->$dungeonId = $dungeonDetails; $this->_numOfDungeons++; }
        }

        return $property;
    }

    /**
     * get all encounters with a specific tier
     *
     * @param  string $tier [ tier of content ]
     * 
     * @return object [ property containing all encounters from a tier ]
     */
    private function _getEncounters($tier) {
        $property = new stdClass();

        foreach( CommonDataContainer::$encounterArray as $encounterId => $encounterDetails ) {
            if ( $encounterDetails->_tier == $tier ) { $property->$encounterId = $encounterDetails; }
        }

        return $property;
    }

    /**
     * get all raid sizes with a specific tier
     * 
     * @return object [ property containing all raid sizes from a tier ]
     */
    private function _getRaidSizes() {
        $property = new stdClass();

        foreach( $this->_dungeons as $dungeonDetails ) {
            $raidSize = $dungeonDetails->_raidSize;

            if ( !isset($this->_raidSizes->$raidSize) ) { $property->$raidSize = $raidSize; }
        }

        return $property;
    }

    /**
     * set tier clear information for guilds
     * 
     * @return void
     */
    public function setTierClears() {
        $tierClearOrderedArr   = array();

        $tierDetails        = CommonDataContainer::$tierArray[$this->_tier];
        $this->_tierFullNum = $tierDetails->_altTier . ' / T' . $tierDetails->_tier;

        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( $guildDetails->_tierDetails->{$this->_tier}->_complete == $this->_numOfEncounters ) {
                $tierCompleteTime = $guildDetails->_tierDetails->{$this->_tier}->_recentTime;
                $tierClearOrderedArr[$guildId] = $tierCompleteTime;

                if ( $guildDetails->_region == 'NA' ) { $this->_numOfNATierClears++; }
                if ( $guildDetails->_region == 'EU' ) { $this->_numOfEUTierClears++; }
                $this->_numOfTierClears++;
            }
        }

        asort($tierClearOrderedArr);

        $firstGuild;
        $recentGuild;

        switch ( $this->_numOfTierClears ) {
            case 0:
                return;
                break;
            case 1:
                reset($tierClearOrderedArr);
                $firstGuild  = CommonDataContainer::$guildArray[key($tierClearOrderedArr)]->_nameLink;
                $recentGuild = CommonDataContainer::$guildArray[key($tierClearOrderedArr)]->_nameLink;
                break;
            default:
                reset($tierClearOrderedArr);
                $firstGuild = CommonDataContainer::$guildArray[key($tierClearOrderedArr)]->_nameLink;
                arsort($tierClearOrderedArr);
                reset($tierClearOrderedArr);
                $recentGuild = CommonDataContainer::$guildArray[key($tierClearOrderedArr)]->_nameLink;
                break;
        }

        if ( isset($firstGuild) ) { $this->_firstTierClear = $firstGuild; }
        if ( isset($recentGuild) ) { $this->_recentTierClear = $recentGuild; }
    }
}