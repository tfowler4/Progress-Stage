<?php
class TwitchDetails {
    protected $_url;
    protected $_image;
    protected $_active;
    protected $_hyperlink;
    protected $_guildDetails;
    protected $_twitchId;

    public function __construct($params) {
        $this->_twitchId     = $params['twitch_id'];
        $this->_url          = $params['twitch_url'];
        $this->_active       = $params['active'];
        $this->_guildDetails = CommonDataContainer::$guildArray[$params['guild_id']];

        $path = ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/twitch/' . $params['twitch_id'];
        $this->_image = '<img src="' . FOLD_TWITCH . $params['twitch_id'] . '">';
        $this->_hyperlink = Functions::generateExternalHyperLink($this->_url, $this->_image, '', false);
    }

    public function __get($name) {
        if ( isset($this->$name) ) {
            return $this->$name;
        }
    }
    
    public function __isset($name) {
        return isset($this->$name);
    }

    public function __destruct() {
        
    }
}