<?php

/**
 * reset controller
 */
class Reset extends Controller {
    /**
     * index model function when page is accessed
     * 
     * @param  array [ url GET parameters ]
     * 
     * @return void
     */
    public function index($params) {
        $this->_view('', $this->_model('Reset', $params));
    }
}