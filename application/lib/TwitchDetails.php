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
    protected $_twitchId;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_twitchId     = $params['twitch_id'];
        $this->_url          = $params['twitch_url'];
        $this->_active       = $params['active'];
        $this->_guildDetails = CommonDataContainer::$guildArray[$params['guild_id']];

        $path = ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/twitch/' . $params['twitch_id'];
        $this->_image = '<img src="' . FOLD_TWITCH . $params['twitch_id'] . '">';
        $this->_hyperlink = Functions::generateExternalHyperLink($this->_url, $this->_image, '', false);
    }
}