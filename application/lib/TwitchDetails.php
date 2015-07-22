<?php
class TwitchDetails {
    protected $_url;
    protected $_imagePath;
    protected $_active;

    public function __construct($params) {
        $this->_url       = $params['twitch_url'];
        $this->_active    = $params['active'];

        $path = ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/twitch/' . $params['twitch_id'];
        $this->_imagePath = '<img src="' . FOLD_TWITCH . $params['twitch_id'] . '">';
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