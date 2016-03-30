<?php

/**
 * twitch stream details detail object
 */
class TwitchDetails extends DetailObject {
    protected $_url;
    protected $_image;
    protected $_active;
    protected $_hyperlink;
    protected $_guildDetails;
    protected $_guildLogo;
    protected $_twitchId;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_twitchId     = $params['twitch_id'];
        $this->_url          = $params['twitch_url'];
        $this->_active       = $params['active'];
        $this->_guildDetails = CommonDataContainer::$guildArray[$params['guild_id']];

        $imgSrc   = FOLD_GUILD_LOGOS . 'logo-' . $this->_guildDetails->_guildId;
        $localSrc = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' .  $this->_guildDetails->_guildId;
        $class    = '';

        if ( file_exists($localSrc) && getimagesize($localSrc) ) {
            $imageDimensions = getimagesize($localSrc);

            $width  = $imageDimensions[0];
            $height = $imageDimensions[1];

            if ( $width > 200 ) {
                $class = 'class="img-responsive media-guild-logo"';
            } else {
                $class = 'style="margin-top: ' . (($height/4)*-1 - 10) . 'px;"';
            }

            $this->_guildLogo = '<img src="' . $imgSrc . '" ' .  $class . ' >';
        }

        $path             = ABS_FOLD_TWITCH . $params['twitch_id'];
        $this->_image     = '<img src="' . FOLD_TWITCH . $params['twitch_id'] . '">';
        $this->_hyperlink = Functions::generateExternalHyperLink($this->_url, $this->_image, '', false);
    }
}