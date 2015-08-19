<?php
class GuildModel extends Model {
    protected $_guildId;
    protected $_name;
    protected $_server;
    protected $_guildDetails;
    protected $_pageDetails;
    protected $_activityArray;
    protected $_latestScreenshot;
    protected $_guildLogo;
    protected $_recentActivity;
    protected $_raidProgressionTableHeader;
    protected $_activityTimelineTableHeader;
    
    const PANE_PROFILE = array(
            'Date Created'    => '_dateCreated',
            'Server'          => '_serverLink',
            'Country'         => '_countryLink',
            'Faction'         => '_faction',
            'Guild Leader(s)' => '_leader',
            'Website'         => '_websiteLink',
            'Social Media'    => '_socialNetworks',
            'World Firsts'    => '_worldFirst',
            'Region Firsts'   => '_regionFirst',
            'Server Firsts'   => '_serverFirst',
            'Status'          => '_active'
        );

    const PANE_NAVIGATION = array(
            'Recent Activity'   => '',
            'Raid Progression'  => '',
            'Activity Timeline' => '',
            'Guild Signature' => ''
        );

    const TABLE_HEADER_PROGRESSION = array(
            'Encounter'      => '_encounterName',
            'Date Completed' => '_datetime',
            'Server'         => '_serverLink',
            'WR'             => '_worldRankImage',
            'RR'             => '_regionRankImage',
            'SR'             => '_serverRankImage',
            'QP'             => '_QP',
            'AP'             => '_AP',
            'APF'            => '_APF',
            'Kill Video'     => '_videoLink',
            'Screenshot'     => '_screenshotLink'
        );

    const TABLE_HEADER_TIMELINE = array(
            'Encounter'      => '_encounterName',
            'Dungeon'        => '_dungeon',
            'Tier'           => '_tier',
            'Date Completed' => '_datetime',
            'Server'         => '_serverLink',
            'Time Diff'      => '_span',
            'WR'             => '_worldRankImage',
            'RR'             => '_regionRankImage',
            'SR'             => '_serverRankImage'
        );

    const PAGE_TITLE = 'Guild Profile';
    const PAGE_NAME  = 'Guild Profile';

    public function __construct($module, $params) {
        parent::__construct();

        if ( isset($params[1]) && $params[1] == 'sig' ) {
            $paramsArray = array_slice($params, 2);

            include 'GuildSignature.php';
            $guildSig = new GuildSignature($paramsArray);
            die;
        }

        $this->title = self::PAGE_TITLE;

        $params = explode('-_-', $params[0]);
        
        /*
         * Possible Inputs: /81, /name, /name-_-server
         */
        
        if ( isset($params[0]) && is_numeric($params[0]) ) { 
            $this->_guildId = $params[0]; 
        } else {
            $this->_name    = $params[0];
        }
        
        if ( isset($params[1]) ) { $this->_server = $params[1]; }

        $this->_guildDetails = $this->getGuildDetails();
        if ( empty($this->_guildDetails) ) { Functions::sendTo404(); }

        $this->_guildDetails->generateEncounterDetails('');
        $this->_guildDetails->generateRankDetails('encounters');
        $this->_activityArray    = $this->getActivityTimeline();
        $this->_latestScreenshot = Template::getScreenshot($this->_guildDetails, $this->_guildDetails->_recentEncounterDetails);

        $this->_raidProgressionTableHeader  = self::TABLE_HEADER_PROGRESSION;
        $this->_activityTimelineTableHeader = self::TABLE_HEADER_TIMELINE;

        if ( !empty($this->_latestScreenshot) ) {
            $this->_recentActivity = $this->getRecentActivityDetails();
        }

        $this->mergeRankDetailsToEncounters();

        $this->title = $this->_guildDetails->_name . ' ' . self::PAGE_NAME;
    }

    public function getGuildDetails() {
        if ( isset($this->_guildId) ) { return CommonDataContainer::$guildArray[$this->_guildId]; }
        if ( isset($this->_name) && !isset($this->_server) ) { return $this->getDetailsByName($this->_name); }
        if ( isset($this->_name) && isset($this->_server) ) { return $this->getDetailsByNameServer($this->_name, $this->_server); }
        return null;
    }
    
    public function getDetailsByName($guildName) {
        $guildName = trim(str_replace("_", " ", $guildName));
    
        foreach ( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( strcasecmp($guildName, $guildDetails->_name) == 0 ) { array_push($guildArray, $guildDetails); 
                return $guildDetails; 
            }
        }
        
        return null;
    }
    
    public function getDetailsByNameServer($guildName, $server) {
        $guildName = trim(str_replace("_", " ", $guildName));
        
        foreach( CommonDataContainer::$guildArray as $guildId => $guildDetails ) {
            if ( strcasecmp($guildName, $guildDetails->_name) == 0 && strcasecmp($server, $guildDetails->_server) == 0 ) {
                return $guildDetails;
            }
        }
    
        return null;
    }

    public function mergeRankDetailsToEncounters() {
        foreach( $this->_guildDetails->_encounterDetails as $encounterId => $encounterDetails ) {
            $newEncounterDetails = new stdClass();

            $encounterProperties = $encounterDetails->getProperties();

            foreach ( $encounterProperties as $key => $value ) {
                $newEncounterDetails->$key = $value;
            }

            foreach ( CommonDataContainer::$rankSystemArray as $systemId => $systemDetails ) {
                $systemAbbreviation = $systemDetails->_abbreviation;
                $identifier         = $encounterId . '_' . $systemAbbreviation;

                $newEncounterDetails->{'_' . $systemAbbreviation} = '--';

                if ( isset($this->_guildDetails->_rankDetails->_rankEncounters->$identifier->_points) ) {
                    $newEncounterDetails->{'_' . $systemAbbreviation} = Functions::formatPoints($this->_guildDetails->_rankDetails->_rankEncounters->$identifier->_points);
                }
            }

            $this->_guildDetails->_encounterDetails->$encounterId = $newEncounterDetails;
        }
    }

    public function getActivityTimeline() {
        $returnArray = array();
        $killTimeArray = array();

        foreach( $this->_guildDetails->_encounterDetails as $encounterDetails ) {
            $strtotime   = $encounterDetails->_strtotime;
            $encounterId = $encounterDetails->_encounterId;

            $killTimeArray[$encounterId] = $strtotime;
        }

        arsort($killTimeArray);

        $currentStrToTime = '';

        foreach( $killTimeArray as $encounterId => $strtotime ) {
            $this->_guildDetails->_encounterDetails->$encounterId->_span = $this->getKillSpan($currentStrToTime, $strtotime);
            $currentStrToTime = $strtotime;

            $returnArray[$encounterId] = $this->_guildDetails->_encounterDetails->$encounterId;
        }

        return $returnArray;
    }

    public function getKillSpan($currentStrToTime, $newStrToTime) {
        $returnSpan = '--';

        if ( empty($currentStrToTime) ) {
            return $returnSpan;
        }

        $currentKillDate = date_create();
        $newKillDate     = date_create();

        date_timestamp_set($currentKillDate, $currentStrToTime);
        date_timestamp_set($newKillDate, $newStrToTime);

        $diff  = date_diff($newKillDate, $currentKillDate);
        $result     = $diff->format("%R%a days ago");
        $months     = $diff->m;
        $hours      = $diff->h;
        $days       = $diff->d;
        $minutes    = $diff->i;
        $time       = '';

        if ( $hours > 0 && $days <= 0 && $months <= 0 ) {
            $minutes    = number_format($minutes / 60, 0, "", "");
            $time       = "-$hours.$minutes Hours";
        } elseif ( $days > 0 && $months <= 0 ) {
            $hours      = number_format($hours / 24, 0, "", "");
            $time       = "-$days.$hours Days";
        } elseif ( $months > 0  ) {
            $number_days    = number_format(($newStrToTime - $currentStrToTime)  / (60 * 60 * 24), 0, "", "");
            $minutes        = number_format($minutes / 60, 0, "", "");
            $time           = "-$number_days.$minutes Days";
        } else {
            $time = "-$minutes Minutes";
        }

        $returnSpan = $time;

        return $returnSpan;
    }

    public function getRecentActivityDetails() {
        $returnArray      = array();
        $activityDetails  = $this->_guildDetails->_recentEncounterDetails;
        $encounterId      = $activityDetails->_encounterId;
        $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

        $returnArray['encounterName'] = $encounterDetails->_name;
        $returnArray['dungeon']       = $encounterDetails->_dungeon;
        $returnArray['datetime']      = $activityDetails->_datetime;
        $returnArray['worldRank']     = 'World ' . Functions::convertToOrdinal($activityDetails->_worldRank);
        $returnArray['regionRank']    = $this->_guildDetails->_region . ' ' . Functions::convertToOrdinal($activityDetails->_regionRank);
        $returnArray['serverRank']    = $this->_guildDetails->_server . ' ' . Functions::convertToOrdinal($activityDetails->_serverRank);

        return $returnArray;
    }

    public function generateInternalAnchor($location) {
        $url       = '#' . $location;
        $url       = strtolower(str_replace(" ", "-", $url)) . '-anchor';
        $hyperlink = '';

        $hyperlink = '<a href="' . $url . '">' . $location . '</a>';

        return $hyperlink;
    }
}