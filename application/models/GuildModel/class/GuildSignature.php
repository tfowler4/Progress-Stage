<?php

Header('Content-type: image/png');
Header('Cache-Control: no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
Header('Expires: Thu, 19 Nov 1981 08:52:00 GMT');
Header('Pragma: no-cache');

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

    /**
     * constructor
     */
    public function __construct($params) {
        if ( isset($params[0]) ) { $this->_guildId    = $params[0]; } else { die; }
        if ( isset($params[1]) ) { $this->_dungeonId  = $params[1]; } else { die; }
        if ( isset($params[2]) ) { $this->_rankSystem = $params[2]; } else { die; }
        if ( isset($params[3]) ) { $this->_view       = $params[3]; } else { die; }
        $this->getDetails();
    }

    /**
     * get guild details details object
     * 
     * @return void
     */
    public function getDetails() {
        if ( !isset(CommonDataContainer::$guildArray[$this->_guildId]) ) { die; }

        $this->_guildDetails = CommonDataContainer::$guildArray[$this->_guildId];
        $this->_guildDetails->generateEncounterDetails('');
        $this->_guildDetails->generateRankDetails('dungeons', $this->_dungeonId);
        $this->_dungeonDetails = CommonDataContainer::$dungeonArray[$this->_dungeonId];

        $this->getHTML($this->_guildDetails);
    }

    /**
     * get html of signature image
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return void
     */
    public function getHTML($guildDetails) {
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
        $imagePath = FOLD_WIDGETS . 'bg_widget_' . strtolower($this->_guildDetails->_faction) . '.png';

        if ( file_exists($imagePath) ) {
            $image = imagecreatefrompng($imagePath);
        }

        imageAlphaBlending($image, true);
        imageSaveAlpha($image, true);

        $fontArialRegular   = FOLD_FONTS . 'ARIAL.TTF';
        $fontArialBold      = FOLD_FONTS . 'ARIALBD.TTF';
        $fontArialBlack     = FOLD_FONTS . 'ARIBLK.TTF';
        $fontVerdanaRegular = FOLD_FONTS . 'VERDANA.TTF';
        $fontVerdanaBold    = FOLD_FONTS . 'VERDANAB.TTF';
        $fontVerdanaItalic  = FOLD_FONTS . 'VERDANAI.TTF';
        $white              = imagecolorallocate($image, 255, 255, 255);
        $startingPoint      = 390;
        $nameText           = $this->_guildDetails->_name;
        $rankText           = 'N/A';
        $standingText       = '0/' . $this->_dungeonDetails->_numOfEncounters . ' ' . $this->_dungeonDetails->_abbreviation;
        $rankSystemText     = CommonDataContainer::$rankSystemArray[$this->_rankSystem]->_name;

        if ( $this->_guildDetails->_dungeonDetails->{$this->_dungeonId}->_complete > 0 ) {
            $standingText = $this->_guildDetails->_dungeonDetails->{$this->_dungeonId}->_standing;
        }

        if ( isset($this->_guildDetails->_rankDetails->_rankDungeons->{$this->_dungeonId . '_' . $this->_rankSystem}) ) {
            $rankDetails = $this->_guildDetails->_rankDetails->_rankDungeons->{$this->_dungeonId . '_' . $this->_rankSystem};
            $rankText    = $this->_viewText . ' ' . Functions::convertToOrdinal($rankDetails->_rank->{$this->_view});
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
}