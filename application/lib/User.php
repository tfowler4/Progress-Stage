<?php

/**
 * user data object
 */
class User extends DataObject {
    protected $_userId;
    protected $_userName;
    protected $_emailAddress;
    protected $_encrypedPassword;
    protected $_dateCreated;
    protected $_admin;

    /**
     * constructor
     */
    public function __construct($params) {
        $this->_userId           = $params['user_id'];
        $this->_userName         = $params['username'];
        $this->_emailAddress     = $params['email'];
        $this->_encrypedPassword = $params['passcode'];
        $this->_dateCreated      = $params['date_joined'];
        $this->_admin            = $params['admin'];
    }
}