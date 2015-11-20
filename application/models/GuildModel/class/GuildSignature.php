<?php

/**
 * guild signature image generator
 */
class GuildSignature {
    protected $_guildId;
    protected $_rankSystem;
    protected $_view;
    protected $_viewText;
    protected $_dungeonId;
    protected $_sigDetails;
    protected $_guildDetails;
    protected $_dungeonDetails;
    protected $_rankDetails;

    /**
     * constructor
     */
    public function __construct($params) {
        Header('Content-type: image/png');
        Header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        Header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
        Header('Pragma: no-cache');

        if ( isset($params[0]) ) { $this->_guildId    = $params[0]; } else { die; }
        if ( isset($params[1]) ) { $this->_dungeonId  = $params[1]; } else { die; }
        if ( isset($params[2]) ) { $this->_rankSystem = $params[2]; } else { die; }
        if ( isset($params[3]) ) { $this->_view       = $params[3]; } else { die; }

        $this->_getDetails();
    }

    /**
     * get guild details details object
     * 
     * @return void
     */
    protected function _getDetails() {
        if ( !isset(CommonDataContainer::$guildArray[$this->_guildId]) ) { die; }

        $this->_guildDetails = CommonDataContainer::$guildArray[$this->_guildId];
        $this->_rankDetails  = $this->_getRankingsForGuild($this->_dungeonId, $this->_rankSystem, $this->_view, $this->_guildId);

        $this->_dungeonDetails = CommonDataContainer::$dungeonArray[$this->_dungeonId];

        $this->_getHTML($this->_guildDetails);
    }

    /**
     * get html of signature image
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return void
     */
    protected function _getHTML($guildDetails) {
        $boxName    = array();
        $boxRank    = array();
        $boxPoints  = array();
        $boxContent = array();

        switch ($this->_view) {
            case '0':
                $this->_view     = '_world';
                $this->_viewText = 'World';
                break;
            case '1':
                $this->_view     = '_region';
                $this->_viewText = $guildDetails->_region;
                break;
            case '2':
                $this->_view     = '_server';
                $this->_viewText = $guildDetails->_server;
                break;
            case '3':
                $this->_view     = '_country';
                $this->_viewText = $guildDetails->_country;
                break;
        }

        $image     = '';
        $imagePath = ABS_FOLD_WIDGETS . 'bg_widget_' . strtolower($this->_guildDetails->_faction) . '.png';

        if ( file_exists($imagePath) ) {
            $image = imagecreatefrompng($imagePath);
        }

        imageAlphaBlending($image, true);
        imageSaveAlpha($image, true);

        $fontArialRegular   = ABS_FOLD_FONTS . 'ARIAL.TTF';
        $fontArialBold      = ABS_FOLD_FONTS . 'ARIALBD.TTF';
        $fontArialBlack     = ABS_FOLD_FONTS . 'ARIBLK.TTF';
        $fontVerdanaRegular = ABS_FOLD_FONTS . 'VERDANA.TTF';
        $fontVerdanaBold    = ABS_FOLD_FONTS . 'VERDANAB.TTF';
        $fontVerdanaItalic  = ABS_FOLD_FONTS . 'VERDANAI.TTF';
        $white              = imagecolorallocate($image, 255, 255, 255);
        $startingPoint      = 390;
        $nameText           = $this->_guildDetails->_name;
        $rankText           = 'N/A';
        $standingText       = '0/' . $this->_dungeonDetails->_numOfEncounters . ' ' . $this->_dungeonDetails->_abbreviation;
        $rankSystemText     = CommonDataContainer::$rankSystemArray[$this->_rankSystem]->_name;

        if ( !empty($this->_rankDetails) ) {
            $standingText = $this->_rankDetails->_progress;
            $rankText = $this->_viewText . ' ' . Functions::convertToOrdinal($this->_rankDetails->_rank);
        }

        // Add Flag and Region/Server text
        $imageFlag = imagecreatefrompng(FOLD_FLAGS . strtolower($this->_guildDetails->_country) . '.png');
        imagecopymerge($image, $imageFlag, 60, '7.5', 0, 0, 29, 16, 100);
        imageTTFText($image, 9.5, 0, 92, 19, $white, $fontVerdanaItalic, $this->_guildDetails->_region . '-' . $this->_guildDetails->_server);

        $boxName = imagettfbbox("13.5", 0, $fontArialBold, $nameText);
        imageTTFText($image, "13.5", 0, 60, 40, $white, $fontArialBold, $nameText);
        imageTTFText($image, "7.5", 0, 60 + ($boxName[2] + 7), 40, $white, $fontArialBold, $standingText);

        $boxRank = imagettfbbox(12, 0, $fontArialBlack, $rankText);
        imageTTFText($image, 12, 0, $startingPoint + (599 - $startingPoint - $boxRank[2]) / 2, 20, $white, $fontArialBlack, $rankText);

        $boxContent = imagettfbbox("7.5", 0, $fontVerdanaBold, $rankSystemText);
        imageTTFText($image, "7.5", 0, $startingPoint + (599 - $startingPoint - $boxContent[2]) / 2, 31, $white, $fontVerdanaBold, $rankSystemText);

        $boxSite = imagettfbbox("6.5", 0, $fontVerdanaBold, HOST_NAME);
        imageTTFText($image, "6.5", 0, $startingPoint + (599 - $startingPoint - $boxSite[2]) / 2, 42, $white, $fontVerdanaBold, HOST_NAME); 

        imagepng($image);
        imagedestroy($image);
    }

    /**
     * get a list of all standing data for a specific dungeon
     *
     * @param  string  $dungeonId [ id for dungeon ]
     * @param  string  $limit     [ limit number of guilds to get ]
     * @param  string  $view      [ viewing type of world/region/country/server ]
     * @param  integer $guildId   [ id of guild ]
     * 
     * @return void
     */
    protected function _getRankingsForGuild($dungeonId, $rankType, $view, $guildId) {
        $dbh = DbFactory::getDbh();

        $query = $dbh->query(sprintf(
                    "SELECT %s.guild_id,
                            %s.dungeon_id,
                            %s.recent_time,
                            %s.recent_activity,
                            %s.world_first,
                            %s.region_first,
                            %s.server_first,
                            %s.country_first,
                            %s.progress,
                            %s.complete,
                            %s.special_progress,
                            qp_points,
                            qp_world_rank,
                            qp_region_rank,
                            qp_server_rank,
                            qp_country_rank,
                            qp_world_trend,
                            qp_region_trend,
                            qp_server_trend,
                            qp_country_trend,
                            qp_world_prev_rank,
                            qp_region_prev_rank,
                            qp_server_prev_rank,
                            qp_country_prev_rank,
                            ap_points,
                            ap_world_rank,
                            ap_region_rank,
                            ap_server_rank,
                            ap_country_rank,
                            ap_world_trend,
                            ap_region_trend,
                            ap_server_trend,
                            ap_country_trend,
                            ap_world_prev_rank,
                            ap_region_prev_rank,
                            ap_server_prev_rank,
                            ap_country_prev_rank,
                            apf_points,
                            apf_world_rank,
                            apf_region_rank,
                            apf_server_rank,
                            apf_country_rank,
                            apf_world_trend,
                            apf_region_trend,
                            apf_server_trend,
                            apf_country_trend,
                            apf_world_prev_rank,
                            apf_region_prev_rank,
                            apf_server_prev_rank,
                            apf_country_prev_rank
                       FROM %s
            LEFT OUTER JOIN %s
                         ON %s.guild_id = %s.guild_id
                         WHERE %s.dungeon_id = %d
                           AND %s.dungeon_id = %d",
            DbFactory::TABLE_RANKINGS,
            DbFactory::TABLE_RANKINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_RANKINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_RANKINGS,
            DbFactory::TABLE_STANDINGS,
            DbFactory::TABLE_RANKINGS,
            $dungeonId,
            DbFactory::TABLE_STANDINGS,
            $dungeonId,
            DbFactory::TABLE_STANDINGS,
            $guildId
        ));

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            return new RankingsDataObject($row, $rankType, $view);
        }
    }
}