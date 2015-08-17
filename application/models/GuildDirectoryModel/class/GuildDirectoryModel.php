<?php
class GuildDirectoryModel extends Model {
    protected $_guildArray;
    protected $_newGuildsArr;
    protected $_guildListing;
    protected $_detailsPane;

    const PAGE_TITLE = 'Guild Directory';
    const TABLE_HEADER = array(
            'Guild'           => '_nameLink',
            'Server'          => '_serverLink',
            'Raid Schedule'   => '_schedule',
            'WF'              => '_worldFirst',
            'RF'              => '_regionFirst',
            'SF'              => '_serverFirst',
            'Recent Activity' => '_recentActivity',
            'Website'         => '_websiteLink',
        );

    const PANE_DATA = array(
            'Total Number of Guilds'         => 'numOfGuilds',
            'Active North American Guilds'   => 'numOfActiveNAGuilds',
            'Inactive North American Guilds' => 'numOfInactiveNAGuilds',
            'Active European Guilds'         => 'numOfActiveEUGuilds',
            'Inactive European Guilds'       => 'numOfInactiveEUGuilds'
        );

    public function __construct($module, $params) {
        parent::__construct($module);

        $this->title = self::PAGE_TITLE;

        $this->_guildArray = CommonDataContainer::$guildArray;

        $this->_guildListing    = $this->getListing();
        $this->_newGuildsArr    = $this->getNewestGuilds();
        $this->_detailsPane     = $this->getGuildData();
    }

    public function getListing() {
        $sortArray = array();

        foreach ( $this->_guildArray as $guildId => $guildDetails ) {
            $guildDetails->generateEncounterDetails('');
            $region = $guildDetails->_region;
            $active = $guildDetails->_active;

            $sortArray[$active][$region][$guildId] = $guildDetails->_name;
        }

        ksort($sortArray); // Sort Regions

        foreach ( $sortArray as $active => $region ) {
            krsort($sortArray[$active]); // Active -> Inactive

            foreach ( $sortArray[$active] as $region => $guildArr ) {
                asort($sortArray[$active][$region]); // A - Z
            }
        }

        foreach ( $sortArray as $active => $region ) {
            foreach ( $sortArray[$active] as $region => $guildArr ) {
                foreach ( $sortArray[$active][$region] as $guildId => $name ) {
                    $sortArray[$active][$region][$guildId] = $this->_guildArray[$guildId];
                }
            }
        }

        return $sortArray;
    }

    public function getNewestGuilds() {
        $sortArray    = array();
        $returnArray  = array();

        foreach ( $this->_guildArray as $guildId => $guildDetails ) {
            $sortArray[$guildId] = $guildDetails->_dateCreated;
        }

        arsort($sortArray);

        $limit = 3; 
        $count = 0;

        foreach ( $sortArray as $guildId => $dateCreated ) {
            if ( $count == $limit ) { break; }

            $returnArray[$guildId] = $this->_guildArray[$guildId];
            $count++;
        }

        return $returnArray;
    }

    public function getGuildData() {
        $returnObj = new stdClass();

        $returnObj->numOfGuilds = count($this->_guildArray);

        foreach ( $this->_guildArray as $guildId => $guildDetails ) {
            if ( $guildDetails->_region == 'NA' ) { 
                if ( !isset($returnObj->numOfActiveNAGuilds) ) { 
                    $returnObj->numOfActiveNAGuilds = 0; 
                }

                if ( !isset($returnObj->numOfInactiveNAGuilds) ) { 
                    $returnObj->numOfInactiveNAGuilds = 0; 
                }

                if ( $guildDetails->_active == 'Inactive' ) {
                    $returnObj->numOfInactiveNAGuilds++; 
                } else {
                    $returnObj->numOfActiveNAGuilds++; 
                }
            }

            if ( $guildDetails->_region == 'EU' ) { 
                if ( !isset($returnObj->numOfActiveEUGuilds) ) { 
                    $returnObj->numOfActiveEUGuilds = 0; 
                }

                if ( !isset($returnObj->numOfInactiveEUGuilds) ) { 
                    $returnObj->numOfInactiveEUGuilds = 0; 
                }

                if ( $guildDetails->_active == 'Inactive' ) {
                    $returnObj->numOfInactiveEUGuilds++; 
                } else {
                    $returnObj->numOfActiveEUGuilds++; 
                }
            }
        }

        return $returnObj;
    }
}