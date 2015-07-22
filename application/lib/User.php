<?php
class User {
    protected $_userId;
    protected $_userName;
    protected $_emailAddress;
    protected $_encrypedPassword;
    protected $_dateCreated;
    protected $_admin;

    public function __construct($params) {
        $this->_userId           = $params['user_id'];
        $this->_userName         = $params['username'];
        $this->_emailAddress     = $params['email'];
        $this->_encrypedPassword = $params['passcode'];
        $this->_dateCreated      = $params['date_joined'];
        $this->_admin            = $params['admin'];
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