<?php

/**
 * terms of service controller
 */
class TermsOfService extends Controller {
    /**
     * index model function when page is accessed
     * 
     * @param  array [ url GET parameters ]
     * 
     * @return void
     */
    public function index($params) {
        $this->_view('', $this->_model('TermsOfService', $params));
    }
}