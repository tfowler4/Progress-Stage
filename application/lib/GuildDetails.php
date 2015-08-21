<?php

/**
 * guild details detail object
 */
class GuildDetails extends DetailObject {
    // Standard Properties
    protected $_guildId;
    protected $_name;
    protected $_nameLink;
    protected $_dateCreated;
    protected $_leader;
    protected $_website;
    protected $_websiteLink;
    protected $_facebook;
    protected $_facebookLink;
    protected $_twitter;
    protected $_twitterLink;
    protected $_faction;
    protected $_region;
    protected $_country;
    protected $_countryImage;
    protected $_countryLink;
    protected $_server;
    protected $_serverLink;
    protected $_active;
    protected $_activeStatus;
    protected $_type;
    protected $_creatorId;
    protected $_parent;
    protected $_child;
    protected $_logo;
    protected $_schedule;
    protected $_guildType;
    protected $_socialNetworks;

    // Ranking Properties
    protected $_progression;
    protected $_rankTier;
    protected $_rankSize;
    protected $_rankDungeon;
    protected $_rankEncounter;
    protected $_rankTierRaidSize;
    protected $_rankOverall;
    protected $_rankDetails;

    // Detail Properties
    protected $_encounterDetails;
    protected $_dungeonDetails;
    protected $_tierDetails;
    protected $_raidSizeDetails;
    protected $_tierRaidSizeDetails;
    protected $_datetime;
    protected $_screenshot;
    protected $_video;
    protected $_strtotime;

    // Merged Standings Properties
    protected $_recentActivity;
    protected $_recentEncounterDetails;
    protected $_recentTime;
    protected $_complete;
    protected $_standing;
    protected $_hardModeComplete;
    protected $_hardModeStanding;
    protected $_conqeuror;
    protected $_timeDiff;
    protected $_worldFirst = 0;
    protected $_regionFirst = 0;
    protected $_serverFirst = 0;

    // Merged Rankings Properties
    protected $_rank;
    protected $_trend;
    protected $_prevRank;
    protected $_pointDiff;
    protected $_worldRank;
    protected $_regionRank;
    protected $_serverRank;
    protected $_worldRankImage = '--';
    protected $_regionRankImage = '--';
    protected $_serverRankImage = '--';

    /**
     * constructor
     */
    public function __construct(&$params) {
        $this->_guildId          = $params['guild_id'];
        $this->_name             = $params['name'];
        $this->_dateCreated      = $params['date_created'];
        $this->_leader           = $params['leader'];
        $this->_website          = $params['website'];
        $this->_facebook         = $params['facebook'];
        $this->_twitter          = $params['twitter'];
        $this->_faction          = $params['faction'];
        $this->_region           = $params['region'];
        $this->_country          = $params['country'];
        $this->_countryImage     = Functions::getImageFlag($this->_country, '');
        $this->_server           = $params['server'];
        $this->_active           = $params['active'];
        $this->_type             = $params['type'];
        $this->_creatorId        = $params['creator_id'];
        $this->_parent           = $params['parent'];
        $this->_child            = $params['child'];
        $this->_schedule         = $params['schedule'];
        $this->_progression      = $params['progression'];
        $this->_rankTier         = $params['rank_tier'];
        $this->_rankSize         = $params['rank_size'];
        $this->_rankDungeon      = $params['rank_dungeon'];
        $this->_rankEncounter    = $params['rank_encounter'];
        $this->_rankTierRaidSize = $params['rank_tier_size'];
        $this->_rankOverall      = $params['rank_overall'];
        $this->_guildType        = (isset($params['guild_type']) && !empty($params['guild_type']) ? $params['guild_type'] : 'N/A');
        $this->_recentActivity   = 'N/A';
        $this->_complete         = 0;
        $this->_standing         = '0/0';
        $this->_hardModeStanding = '0/0';
        $this->_conqeuror        = 'No';
        $this->_logo             = Template::getLogo($this);

        // set guild active status
        if ( $this->_active == '1' ) { $this->_activeStatus = 'Active'; }
        if ( $this->_active == '0' ) { $this->_activeStatus = 'Inactive'; }

        if ( $this->_activeStatus == 'Active' ) {
            $this->_nameLink = $this->_countryImage . '<span>' . Functions::generateInternalHyperLink('guild', '', $this->_server, $this->_name, '') . '</span>';
        } else {
            $this->_nameLink = $this->_countryImage . '<span>' . Functions::generateInternalHyperLink('guild', $this->_faction, $this->_server, $this->_name, '') . '</span>';
        }

        // set server image and text
        $serverDetails  = CommonDataContainer::$serverArray[$this->_server];
        $this->_serverLink  = $serverDetails->_nameLink;

        // set country image and text
        $countryDetails = CommonDataContainer::$countryArray[$this->_country];
        $this->_countryLink = $this->_countryImage . '<span>' . $countryDetails->_name . '</span>';

        // set other guild options to either N/A or actual links
        if ( empty($this->_leader) )    { $this->_leader       = 'N/A'; }
        if ( empty($this->_website) )   { $this->_websiteLink  = 'N/A'; } else { $this->_websiteLink = Functions::generateExternalHyperLink($this->_website, 'View', ''); }
        if ( empty($this->_schedule) )  { $this->_schedule     = 'N/A'; } else { $this->_schedule = $this->_schedule; }
        if ( !empty($this->_facebook) ) { $this->_facebookLink = Functions::generateExternalHyperLink($this->_facebook, IMG_FACEBOOK_SMALL_LOGO, ''); }
        if ( !empty($this->_twitter) )  { $this->_twitterLink  = Functions::generateExternalHyperLink($this->_twitter, IMG_TWITTER_SMALL_LOGO, ''); }

        // combine social network links
        $this->_socialNetworks = $this->_facebookLink . ' ' . $this->_twitterLink;
        if ( empty(trim($this->_socialNetworks)) ) { $this->_socialNetworks = 'N/A'; }

        // set default standings detail info
        $this->_dungeonDetails = $this->generateDungeonDetails();

        // TODO: Add Tier/Raid Size/Tier Raid Size content
        //$this->_tierDetails         = $this->generateTierDetails();
        //$this->_tierRaidSizeDetails = $this->generateTierRaidSizeDetails();
    }

    /**
     * specify a specific character limit for guild name when displaying
     * 
     * @param  integer $textLimit [ number of characters ]
     * 
     * @return void
     */
    public function nameLength($textLimit) {
        if ( $this->_active == 'Inactive' ) {
            $this->_nameLink = $this->_countryImage . '<span>' . Functions::generateInternalHyperLink('guild', '', $this->_server, $this->_name, $textLimit) . '</span>';
        } else {
            $this->_nameLink = $this->_countryImage . '<span>' . Functions::generateInternalHyperLink('guild', $this->_faction, $this->_server, $this->_name, $textLimit) . '</span>';
        }
    }

    /**
     * populate rankDetails property with all details from database
     * 
     * @param  string $dataType [ specify which ranking details to generate ex. encounters ]
     * @param  string $dataId   [ specify the id for a specific dungeon/encounter ]
     * 
     * @return void
     */
    public function generateRankDetails($dataType, $dataId = null) {
        $property = new stdClass();
        
        if ( $dataType == 'encounters' ) {
            // Generate Encounter Ranking
            $rankEncounters = new stdClass();
            $encounterArray = explode("~~", $this->_rankEncounter);

            foreach ( $encounterArray as $encounter ) {
                 if ( empty($encounter) ) { continue; }

                $encounterDetails = explode('<>', $encounter);
                $encounterId      = $encounterDetails[0];

                $encounterRankArray = explode('++', $encounterDetails[1]);

                foreach( $encounterRankArray as $rankDetails ) {
                    $rankSystemArray = explode('||', $rankDetails);

                    $rankSystem = $rankSystemArray[0];
                    $identifier = $encounterId . '_' . $rankSystem;

                    $rankEncounters->$identifier = new RankDetails($rankSystemArray, $encounterId);
                }
            }

            $property->_rankEncounters = $rankEncounters;
        } elseif ( $dataType == 'dungeons' ) {
            // Generate Dungeon Ranking
            $rankDungeons = new stdClass();
            $dungeonArray = explode('~~', $this->_rankDungeon);

            foreach ( $dungeonArray as $dungeon ) {
                if ( empty($dungeon) ) { continue; }

                $dungeonDetails = explode('<>', $dungeon);
                $dungeonId      = $dungeonDetails[0];

                $dungeonRankArray = explode('++', $dungeonDetails[1]);

                foreach( $dungeonRankArray as $rankDetails ) {
                    $rankSystemArray = explode('||', $rankDetails);
                    
                    $rankSystem = $rankSystemArray[0];
                    $identifier = $dungeonId . '_' . $rankSystem;

                    $rankDungeons->$identifier = new RankDetails($rankSystemArray, $dungeonId);
                }
            }

            $property->_rankDungeons = $rankDungeons;
        }

        $this->_rankDetails = $property;
    }

    /**
     * create empty tier raid size details to store in tierRaidSizeDetails property
     * 
     * @return object [ property containing empty tierRaidSize detail objects ]
     */
    public function generateTierRaidSizeDetails() {
        $property = new stdClass();

        foreach ( CommonDataContainer::$tierRaidSizeArray as $tierRaidSize => $tierRaidSizeDetails ) {
            $property->$tierRaidSize = new TierRaidSizeDetails($tierRaidSizeDetails);
        }
            
        return $property;
    }

    /**
     * create empty tier details to store in tierDetails property
     * 
     * @return object [ property containing empty tier detail objects ]
     */
    public function generateTierDetails() {
        $property = new stdClass();
            
        foreach ( CommonDataContainer::$tierArray as $tierId => $tierDetails ) {
            $property->{$tierDetails->_tier} = new TierDetails($tierDetails);
        }

        return $property;
    }

    /**
     * create empty dungeon details to store in dungeonDetails property
     * 
     * @return object [ property containing empty dungeon detail objects ]
     */
    public function generateDungeonDetails() {
        $property = new stdClass();
        
        foreach ( CommonDataContainer::$dungeonArray as $dungeonId => $dungeonDetails ) {
            $property->$dungeonId = new DungeonDetails($dungeonDetails);
        }
        
        return $property;
    }
    
    public function generateEncounterDetails($dataType, $dataId = null) {
        $property = new stdClass();
        
        if ( isset($this->_progression) && !empty($this->_progression) ) {
            $progressionArr = explode("~~", $this->_progression);

            $numOfProgression = count($progressionArr);
            for ( $count = 0; $count < $numOfProgression; $count++ ) {
                $progressionDetails  = explode('||', $progressionArr[$count]);
                $encounterId         = $progressionDetails[0];
                $encounterDetails    = CommonDataContainer::$encounterArray[$encounterId];
                $dungeonId           = $encounterDetails->_dungeonId;
                $dungeonDetails      = CommonDataContainer::$dungeonArray[$dungeonId];
                //$tierId              = $dungeonDetails->_tier;
                //$tierDetails         = CommonDataContainer::$tierArray[$tierId];
                //$raidSize            = $dungeonDetails->_raidSize;
                //$tierRaidSize        = $tierDetails->_tier . '_' . $raidSize;
                //$tierRaidSizeDetails = CommonDataContainer::$tierRaidSizeArray[$tierRaidSize];

                $totalNumOfEncounters    = count(CommonDataContainer::$encounterArray);
                $totalNumOfSpcEncounters = count(CommonDataContainer::$encounterArray);

                // Create EncounterDetails Object
                if ( $dataType == 'encounter' && $encounterId != $dataId ) { continue; }
                
                if ( $dataType == 'dungeon' && $dungeonId != $dataId ) { continue; }

                //if ( $dataType == 'tier' && $tierId != $dataId ) { continue; }

                if ( $dataType == 'tierRaidSize' && $tierRaidSize != $dataId ) { continue; }

                $encounter              = new EncounterDetails($progressionDetails, $this, $dungeonDetails);
                $property->$encounterId = $encounter;

                // Do not process if encounterDetails already exist
                if ( isset($this->_encounterDetails->$encounterId) ) {
                    continue; 
                }

                $this->updateCompletedCount($dungeonDetails, $encounterDetails->_type, $this); // Increase Complete / Standing
                $this->updateCompletedCount($dungeonDetails, $encounterDetails->_type, $this->_dungeonDetails->$dungeonId); // Increase Dungeon Complete / Standing
                //$this->updateCompletedCount($tierDetails, $encounterDetails->_type, $this->_tierDetails->$tierId); // Increase Tier Complete / Standing
                //$this->updateCompletedCount($tierRaidSizeDetails, $encounterDetails->_type, $this->_tierRaidSizeDetails->$tierRaidSize); // Increase Tier Size Complete / Standing
                
                $this->updateRecentActivity($encounter, $encounterDetails, 'self', ''); // Recent Encounter / Time
                $this->updateRecentActivity($encounter, $encounterDetails, 'dungeon', $dungeonId); // Recent Dungeon Encounter / Time
                //$this->updateRecentActivity($encounter, $encounterDetails, 'tier', $tierId); // Recent Tier Encounter / Time
                //$this->updateRecentActivity($encounter, $encounterDetails, 'tierRaidSize', $tierRaidSize); // Recent Tier Size Encounter / Time

                // Set World/Region/Server First
                if ( $encounter->_serverRank == 1 ) {
                    $this->updateFirstCount('server', $encounterDetails->_type, $this);
                    $this->updateFirstCount('server', $encounterDetails->_type, $this->_dungeonDetails->$dungeonId);
                    //$this->updateFirstCount('server', $encounterDetails->_type, $this->_tierDetails->$tierId);
                    //$this->updateFirstCount('server', $encounterDetails->_type, $this->_tierRaidSizeDetails->$tierRaidSize);
                }

                if ( $encounter->_regionRank == 1 ) {
                    $this->updateFirstCount('region', $encounterDetails->_type, $this);
                    $this->updateFirstCount('region', $encounterDetails->_type, $this->_dungeonDetails->$dungeonId);
                    //$this->updateFirstCount('region', $encounterDetails->_type, $this->_tierDetails->$tierId);
                    //$this->updateFirstCount('region', $encounterDetails->_type, $this->_tierRaidSizeDetails->$tierRaidSize);
                }

                if ( $encounter->_worldRank == 1 ) {
                    $this->updateFirstCount('world', $encounterDetails->_type, $this);
                    $this->updateFirstCount('world', $encounterDetails->_type, $this->_dungeonDetails->$dungeonId);
                    //$this->updateFirstCount('world', $encounterDetails->_type, $this->_tierDetails->$tierId);
                    //$this->updateFirstCount('world', $encounterDetails->_type, $this->_tierRaidSizeDetails->$tierRaidSize);
                }

                if ( $dataType == 'encounter' && $encounterId == $dataId ) {
                    $this->_encounterDetails = $property;
                    return;
                }
            }
        }

        $this->_encounterDetails = $property;
    }

    /**
     * updates standings completion count based on encounterType
     * 
     * @param  string       $dataType      [ specific data view ]
     * @param  string       $encounterType [ encounter type (0/1/2) ]
     * @param  GuildDetails $object        [ dungeon/tier/raidsize/tiersize/overall details object ]
     * 
     * @return void
     */
    public function updateFirstCount($dataType, $encounterType, $object) {
        if ( $encounterType == 0 ) {
            switch ( $dataType ) {
                case 'server':
                    $object->_serverFirst++;
                    break;
                case 'region':
                    $object->_regionFirst++;
                    break;
                case 'world':
                    $object->_worldFirst++;
                    break;
            }
        }
    }

    /**
     * updates standings completion count based on encounterType
     * 
     * @param  object       $objDetails    [ dungeon/tier/raidsize/tiersize/overall details object ]
     * @param  string       $encounterType [ encounter type (0/1/2) ]
     * @param  GuildDetails $guildDetails  [ guild details object ]
     * 
     * @return void
     */
    public function updateCompletedCount($objDetails, $encounterType, $guildDetails) {
        switch ($encounterType) {
            case 0:
                $guildDetails->_complete++;
                $this->updateStandings($guildDetails, $objDetails);
                break;
            case 1:
                $guildDetails->_conqeuror = 'Yes';
                break;
            case 2:
                $guildDetails->_hardModeComplete++;
                $this->updateHardModeStandings($guildDetails, $objDetails);
                break;
        }
    }

    /**
     * update standings for normal encounters
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * @param  Dungeon      $objDetails   [ dungeon object specific details ]
     * 
     * @return void
     */
    public function updateStandings($guildDetails, $objDetails) {
        $guildDetails->_standing = $guildDetails->_complete . '/' . $objDetails->_numOfEncounters . ' ' . $objDetails->_abbreviation;
    }

    /**
     * update standings for hard mode or special encounters
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * @param  Dungeon      $objDetails   [ dungeon object specific details ]
     * 
     * @return void
     */
    public function updateHardModeStandings($guildDetails, $objDetails) {
        $guildDetails->_hardModeStanding = $guildDetails->_hardModeComplete . '/' . $objDetails->_numOfSpecialEncounters;
    }

    /**
     * update the recent raid activity property of the guild details object
     * 
     * @param  EncounterDetails $encounter        [ guild encounter details object ]
     * @param  Encounter        $encounterDetails [ encounter object with encounter specific details ]
     * @param  string           $obj              [ which ]
     * @param  string           $id               [ id of specific encounter/dungeon/etc ]
     * 
     * @return void
     */
    public function updateRecentActivity($encounter, $encounterDetails, $obj, $id) {
        if ( $encounterDetails->_type > 0 ) { return; }

        $recentTime;

        switch ( $obj ) {
            case 'self':
                $recentTime = $this->_recentTime;
                break;
            case 'dungeon':
                $recentTime = $this->_dungeonDetails->$id->_recentTime;
                break;
            case 'tier':
                $recentTime = $this->_tierDetails->$id->_recentTime;
                break;
            case 'tierRaidSize':
                $recentTime = $this->_tierRaidSizeDetails->$id->_recentTime;
                break;
        }

        if ( !isset($recentTime) || $recentTime == "" || ( $recentTime < $encounter->_strtotime ) ) {
            switch ( $obj ) {
                case 'self':
                    $this->_recentTime             = $encounter->_strtotime;
                    $this->_recentActivity         = $encounterDetails->_encounterName . ' @ ' . $encounter->_datetime;
                    $this->_recentEncounterDetails = $encounter;
                    break;
                case 'dungeon':
                    $this->_dungeonDetails->$id->_recentTime             = $encounter->_strtotime;
                    $this->_dungeonDetails->$id->_recentActivity         = $encounterDetails->_encounterName . ' @ ' . $encounter->_datetime;
                    $this->_dungeonDetails->$id->_recentEncounterDetails = $encounter;
                    break;
                case 'tier':
                    $this->_tierDetails->$id->_recentTime             = $encounter->_strtotime;
                    $this->_tierDetails->$id->_recentActivity         = $encounterDetails->_encounterName . ' @ ' . $encounter->_datetime;
                    $this->_tierDetails->$id->_recentEncounterDetails = $encounter;
                    break;
                case 'tierRaidSize':
                    $this->_tierRaidSizeDetails->$id->_recentTime             = $encounter->_strtotime;
                    $this->_tierRaidSizeDetails->$id->_recentActivity         = $encounterDetails->_encounterName . ' @ ' . $encounter->_datetime;
                    $this->_tierRaidSizeDetails->$id->_recentEncounterDetails = $encounter;
                    break;
            }
        }
    }

    /**
     * merge a guild's standing details into the guild object properties
     * 
     * @param  string $dataType [ encounterDetails, dungeonDetails, etc ]
     * @param  string $id       [ id of specific encounter/dungeon/etc ]
     * 
     * @return void
     */
    public function mergeViewDetails($dataType, $id) {
        foreach ($this->$dataType->$id->getProperties() as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * merge ranking details into guild details object properties
     * 
     * @param  string $dataType [ encounterDetails, dungeonDetails, etc ]
     * @param  string $id       [ id of specific encounter/dungeon/etc ]
     * @param  string $view     [ specific list view server/region/world/etc ]
     * 
     * @return void
     */
    public function mergeRankViewDetailsDetails($dataType, $id, $view) {
        foreach ($this->_rankDetails->$dataType->$id->getProperties() as $key => $value) {
            if ( is_a($value, 'RankViewDetails') ) {
                $this->$key = $value->{'_'.$view};

                if ( $key == '_trend' && intval($this->$key) > 0 ) {
                    $this->$key = IMG_ARROW_TREND_UP_SML . '<span>+' . $this->$key . '</span>';
                } elseif ( $key == '_trend' && intval($this->_trend) < 0 ) {
                    $this->$key = IMG_ARROW_TREND_DOWN_SML . '<span>' . $this->$key . '</span>';
                }
            } else {
                $this->$key = $value;
            }
        }
    }

    /**
     * get the difference in unix time between two time values
     * 
     * @param  integer $currentTime [ starting unix time value ]
     * @param  integer $newTime     [ new unix time value ]
     * 
     * @return void
     */
    public function getTimeDiff($currentTime, $newTime) {
        $timeDiff           = $newTime - $currentTime;
        $this->_timeDiff    = Functions::convertToDiffDaysHoursMins($timeDiff);

        if ( $currentTime == 0 ) { 
            $this->_timeDiff = '--'; 
        }
    }

    /**
     * get the difference in point amounts between two point values
     * 
     * @param  double $currentPoints [ starting points value ]
     * @param  double $newPoints     [ new points value ]
     * 
     * @return void
     */
    public function getPointDiff($currentPoints, $newPoints) {
        $pointDiff        = $newPoints - $currentPoints;
        $this->_pointDiff = Functions::formatPoints($pointDiff);

        if ( $currentPoints == 0 ) { 
            $this->_pointDiff = '--'; 
        }
    }
}