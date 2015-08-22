<?php

/**
 * logout of the website page
 */
class LogoutModel extends Model {
    const PAGE_TITLE = 'Logout';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        if ( Post::formActive() ) { // Form has required fields filled out
            session_unset();

            $this->_dialogOptions = array('title' => 'Success', 'message' => 'You have successfully logged out!');
        }
    }
}