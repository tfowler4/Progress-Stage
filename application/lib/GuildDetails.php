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
    protected $_rankTierSize;
    protected $_rankOverall;
    protected $_rankDetails;

    // Detail Properties
    protected $_encounterDetails;
    protected $_dungeonDetails;
    protected $_tierDetails;
    protected $_raidSizeDetails;
    protected $_tierSizeDetails;
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
    protected $_worldRank = '--';
    protected $_regionRank = '--';
    protected $_serverRank = '--';
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
        $this->_rankTierSize     = $params['rank_tier_size'];
        $this->_rankOverall      = $params['rank_overall'];
        $this->_recentActivity   = 'N/A';
        $this->_complete         = 0;
        $this->_standing         = '0/0';
        $this->_hardModeStanding = '0/0';
        $this->_conqeuror        = 'No';
        $this->_logo             = Template::getLogo($this);
        $this->_guildType        = (isset($params['guild_type']) && !empty($params['guild_type']) ? $params['guild_type'] : 'N/A');

        $this->generateTableFields();

        $this->_tierSizeDetails  = $this->generateTierSizeDetails();
        $this->_tierDetails      = $this->generateTierDetails();
        $this->_dungeonDetails   = $this->generateDungeonDetails();
    }

    public function nameLength($textLimit) {
        if ( $this->_active == 'Inactive' ) {
            $this->_nameLink = $this->_countryImage . '<span style="vertical-align:middle;">' . Functions::generateInternalHyperLink('guild', '', $this->_server, $this->_name, $textLimit) . '</span>';
        } else {
            $this->_nameLink = $this->_countryImage . '<span style="vertical-align:middle;">' . Functions::generateInternalHyperLink('guild', $this->_faction, $this->_server, $this->_name, $textLimit) . '</span>';
        }
    }

    public function generateTableFields() {
        $serverDetails  = CommonDataContainer::$serverArray[$this->_server];
        $countryDetails = CommonDataContainer::$countryArray[$this->_country];

        if ( $this->_active == 'Inactive' || $this->_active == '0' ) {
            $this->_nameLink = $this->_countryImage . '<span style="vertical-align:middle;">' . Functions::generateInternalHyperLink('guild', '', $this->_server, $this->_name, '') . '</span>';
        } else {
            $this->_nameLink = $this->_countryImage . '<span style="vertical-align:middle;">' . Functions::generateInternalHyperLink('guild', $this->_faction, $this->_server, $this->_name, '') . '</span>';
        }

        $this->_serverLink  = $serverDetails->_nameLink;
        $this->_countryLink = $this->_countryImage . '<span style="vertical-align:middle;">' . $countryDetails->_name . '</span>';

        if ( empty($this->_leader) )    { $this->_leader = "N/A"; }
        if ( empty($this->_website) )   { $this->_websiteLink = "N/A"; } else { $this->_websiteLink = Functions::generateExternalHyperLink($this->_website, 'View', ''); }
        if ( !empty($this->_facebook) ) { $this->_facebookLink = Functions::generateExternalHyperLink($this->_facebook, IMG_FACEBOOK_SMALL_LOGO, ''); }
        if ( !empty($this->_twitter) )  { $this->_twitterLink = Functions::generateExternalHyperLink($this->_twitter, IMG_TWITTER_SMALL_LOGO, ''); }
        if ( empty($this->_schedule) )  { $this->_schedule = " N/A"; } else { $this->_schedule = $this->_schedule; }
        
        $this->_socialNetworks = $this->_facebookLink . ' ' . $this->_twitterLink;

        if ( empty(trim($this->_socialNetworks)) ) { $this->_socialNetworks = 'N/A'; }

        if ( $this->_active == '1' ) { $this->_active = 'Active'; }
        if ( $this->_active == '0' ) { $this->_active = 'Inactive'; }
    }
    
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
        } elseif ( $dataType == 'tiers' ) {
            // Generate Tier Ranking
            $rankTiers = new stdClass();
            $systemArr = explode("$$", $this->_rankTier);

            for ( $system = 0; $system < count($systemArr); $system++ ) {
                $tiers      = $systemArr[$system];
                $tierArr    = explode('~~', $tiers);
            
                for ( $tier = 0; $tier < count($tierArr); $tier++ ) {
                    $tierDetails    = explode('||', $tierArr[$tier]);
                    $systemAbbrev   = $GLOBALS['point_system_abbrev'][$system];
                        
                    $tierId     = $tierDetails[0];
                    $identifier = $tierId . '_' . $systemAbbrev;

                    $rankTiers->$identifier = new RankDetails($tierDetails, $system);
                }
            }
            $property->_rankTiers = $rankTiers;
        }

        $this->_rankDetails = $property;
    }

    public function generateTierSizeDetails() {
        $property = new stdClass();

        foreach ( CommonDataContainer::$tierSizeArray as $tierSize => $tierSizeDetails ) {
            $property->$tierSize = new TierSizeDetails($tierSizeDetails);
        }
            
        return $property;
    }

    public function generateTierDetails() {
        $property = new stdClass();
            
        foreach ( CommonDataContainer::$tierArray as $tierId => $tierDetails ) {
            $property->{$tierDetails->_tier} = new TierDetails($tierDetails);
        }

        return $property;
    }

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
                $progressionDetails = explode('||', $progressionArr[$count]);
                $encounterId        = $progressionDetails[0];
                $encounterDetails   = CommonDataContainer::$encounterArray[$encounterId];
                $dungeonId          = $encounterDetails->_dungeonId;
                $dungeonDetails     = CommonDataContainer::$dungeonArray[$dungeonId];
                $tierId             = $dungeonDetails->_tier;
                $tierDetails        = CommonDataContainer::$tierArray[$tierId];
                $raidSize           = $dungeonDetails->_raidSize;
                $tierSize           = $tierDetails->_tier . '_' . $raidSize;
                $tierSizeDetails   = CommonDataContainer::$tierSizeArray[$tierSize];

                $totalNumOfEncounters    = count(CommonDataContainer::$encounterArray);
                $totalNumOfSpcEncounters = count(CommonDataContainer::$encounterArray);

                // Create EncounterDetails Object
                if ( $dataType == 'encounter' && $encounterId != $dataId ) { continue; }
                
                if ( $dataType == 'dungeon' && $dungeonId != $dataId ) { continue; }

                if ( $dataType == 'tier' && $tierId != $dataId ) { continue; }

                if ( $dataType == 'tierSize' && $tierSize != $dataId ) { continue; }

                $encounter              = new EncounterDetails($progressionDetails, $this, $dungeonDetails);
                $property->$encounterId = $encounter;

                // Do not process if encounterDetails already exist
                if ( isset($this->_encounterDetails->$encounterId) ) {
                    continue; 
                }

                $this->updateCompletedCount($dungeonDetails, $encounterDetails->_type, $this); // Increase Complete / Standing
                $this->updateCompletedCount($dungeonDetails, $encounterDetails->_type, $this->_dungeonDetails->$dungeonId); // Increase Dungeon Complete / Standing
                $this->updateCompletedCount($tierDetails, $encounterDetails->_type, $this->_tierDetails->$tierId); // Increase Tier Complete / Standing
                $this->updateCompletedCount($tierSizeDetails, $encounterDetails->_type, $this->_tierSizeDetails->$tierSize); // Increase Tier Size Complete / Standing
                
                $this->updateRecentActivity($encounter, $encounterDetails, 'self', ''); // Recent Encounter / Time
                $this->updateRecentActivity($encounter, $encounterDetails, 'dungeon', $dungeonId); // Recent Dungeon Encounter / Time
                $this->updateRecentActivity($encounter, $encounterDetails, 'tier', $tierId); // Recent Tier Encounter / Time
                $this->updateRecentActivity($encounter, $encounterDetails, 'tierSize', $tierSize); // Recent Tier Size Encounter / Time

                // Set World/Region/Server First
                if ( $encounter->_serverRank == 1 ) {
                    $this->updateFirstCount('server', $encounterDetails->_type, $this);
                    $this->updateFirstCount('server', $encounterDetails->_type, $this->_dungeonDetails->$dungeonId);
                    $this->updateFirstCount('server', $encounterDetails->_type, $this->_tierDetails->$tierId);
                    $this->updateFirstCount('server', $encounterDetails->_type, $this->_tierSizeDetails->$tierSize);
                }

                if ( $encounter->_regionRank == 1 ) {
                    $this->updateFirstCount('region', $encounterDetails->_type, $this);
                    $this->updateFirstCount('region', $encounterDetails->_type, $this->_dungeonDetails->$dungeonId);
                    $this->updateFirstCount('region', $encounterDetails->_type, $this->_tierDetails->$tierId);
                    $this->updateFirstCount('region', $encounterDetails->_type, $this->_tierSizeDetails->$tierSize);
                }

                if ( $encounter->_worldRank == 1 ) {
                    $this->updateFirstCount('world', $encounterDetails->_type, $this);
                    $this->updateFirstCount('world', $encounterDetails->_type, $this->_dungeonDetails->$dungeonId);
                    $this->updateFirstCount('world', $encounterDetails->_type, $this->_tierDetails->$tierId);
                    $this->updateFirstCount('world', $encounterDetails->_type, $this->_tierSizeDetails->$tierSize);
                }

                if ( $dataType == 'encounter' && $encounterId == $dataId ) {
                    $this->_encounterDetails = $property;
                    return;
                }
            }
        }

        $this->_encounterDetails = $property;
    }

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

    public function updateStandings($guildDetails, $objDetails) {
        $guildDetails->_standing = $guildDetails->_complete . '/' . $objDetails->_numOfEncounters . ' ' . $objDetails->_abbreviation;
    }

    public function updateHardModeStandings($guildDetails, $objDetails) {
        $guildDetails->_hardModeStanding = $guildDetails->_hardModeComplete . '/' . $objDetails->_numOfSpecialEncounters;
    }

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
            case 'tierSize':
                $recentTime = $this->_tierSizeDetails->$id->_recentTime;
                break;
        }

        if ( !isset($recentTime) || $recentTime == "" || ( $recentTime < $encounter->_strtotime ) ) {
            switch ( $obj ) {
                case 'self':
                    $this->_recentTime             = $encounter->_strtotime;
                    $this->_recentActivity         = $encounterDetails->_encounterName . " @ " . $encounter->_datetime;
                    $this->_recentEncounterDetails = $encounter;
                    break;
                case 'dungeon':
                    $this->_dungeonDetails->$id->_recentTime             = $encounter->_strtotime;
                    $this->_dungeonDetails->$id->_recentActivity         = $encounterDetails->_encounterName . " @ " . $encounter->_datetime;
                    $this->_dungeonDetails->$id->_recentEncounterDetails = $encounter;
                    break;
                case 'tier':
                    $this->_tierDetails->$id->_recentTime             = $encounter->_strtotime;
                    $this->_tierDetails->$id->_recentActivity         = $encounterDetails->_encounterName . " @ " . $encounter->_datetime;
                    $this->_tierDetails->$id->_recentEncounterDetails = $encounter;
                    break;
                case 'tierSize':
                    $this->_tierSizeDetails->$id->_recentTime             = $encounter->_strtotime;
                    $this->_tierSizeDetails->$id->_recentActivity         = $encounterDetails->_encounterName . " @ " . $encounter->_datetime;
                    $this->_tierSizeDetails->$id->_recentEncounterDetails = $encounter;
                    break;
            }
        }
    }

    public function mergeViewDetails($dataType, $id) {
        foreach ($this->$dataType->$id->getProperties() as $key => $value) {
            $this->$key = $value;
        }
    }

    public function mergeRankViewDetails($dataType, $id, $view) {
        foreach ($this->_rankDetails->$dataType->$id->getProperties() as $key => $value) {
            if ( is_a($value, 'RankView') ) {
                $this->$key = $value->{'_'.$view};

                if ( $key == '_trend' && intval($this->$key) > 0 ) {
                    $this->$key = $GLOBALS['images']['trend_up'] . '<span>+' . $this->$key . '</span>';
                } elseif ( $key == '_trend' && intval($this->_trend) < 0 ) {
                    $this->$key = $GLOBALS['images']['trend_down'] . '<span>' . $this->$key . '</span>';
                }
            } else {
                $this->$key = $value;
            }        
        }
    }

    public function getTimeDiff($currentTime, $newTime) {
        $timeDiff           = $newTime - $currentTime;
        $this->_timeDiff    = Functions::convertToDiffDaysHoursMins($timeDiff);

        if ( $currentTime == 0 ) { 
            $this->_timeDiff = '--'; 
        }
    }

    public function getPointDiff($currentPoints, $newPoints) {
        $pointDiff        = $newPoints - $currentPoints;
        $this->_pointDiff = Functions::formatPoints($pointDiff);

        if ( $currentPoints == 0 ) { 
            $this->_pointDiff = '--'; 
        }
    }
}