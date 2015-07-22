<?php
class GuildDirectoryModel extends Model {
    protected $_guildArr;
    protected $_newGuildsArr;
    protected $_guildListing;
    protected $_detailsPane;
    
    public function __construct($module, $params) {
        parent::__construct($module);
        
        $this->_guildArr = CommonDataContainer::$guildArray;

        $this->_guildListing    = $this->getListing();
        $this->_newGuildsArr    = $this->getNewestGuilds();
        $this->_detailsPane     = $this->getGuildData();
    }

    public function getListing() {
        $sortArr = array();

        foreach ( $this->_guildArr as $guildId => $guildDetails ) {
            $region = $guildDetails->_region;
            $active = $guildDetails->_active;


            $sortArr[$active][$region][$guildId] = $guildDetails->_name;
        }

        ksort($sortArr); // Sort Regions

        foreach ( $sortArr as $active => $region ) {
            krsort($sortArr[$active]); // Active -> Inactive

            foreach ( $sortArr[$active] as $region => $guildArr ) {
                asort($sortArr[$active][$region]); // A - Z
            }
        }

        foreach ( $sortArr as $active => $region ) {
            foreach ( $sortArr[$active] as $region => $guildArr ) {
                foreach ( $sortArr[$active][$region] as $guildId => $name ) {
                    $sortArr[$active][$region][$guildId] = $this->_guildArr[$guildId];
                }
            }
        }

        return $sortArr;
    }

    public function getNewestGuilds() {
        $sortArr    = array();
        $returnArr  = array();

        foreach ( $this->_guildArr as $guildId => $guildDetails ) {
            $sortArr[$guildId] = $guildDetails->_dateCreated;
        }

        arsort($sortArr);

        $limit = 3; 
        $count = 0;

        foreach ( $sortArr as $guildId => $dateCreated ) {
            if ( $count == $limit ) { break; }

            $returnArr[$guildId] = $this->_guildArr[$guildId];
            $count++;
        }

        return $returnArr;
    }

    public function getGuildData() {
        $returnObj = new stdClass();

        $returnObj->numOfGuilds = count($this->_guildArr);

        foreach ( $this->_guildArr as $guildId => $guildDetails ) {
            if ( $guildDetails->_region == 'NA' ) { 
                if ( !isset($returnObj->numOfNAGuilds) ) { 
                    $returnObj->numOfNAGuilds = 0; 
                } 

                $returnObj->numOfNAGuilds++; 
            }

            if ( $guildDetails->_region == 'EU' ) { 
                if ( !isset($returnObj->numOfEUGuilds) ) { 
                    $returnObj->numOfEUGuilds = 0; 
                } 

                $returnObj->numOfEUGuilds++; 
            }
        }

        return $returnObj;
    }
}