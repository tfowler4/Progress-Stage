<?php
class App {
    protected $_controller = 'news';
    protected $_method     = 'index';
    protected $_params     = array();

    public function __construct() {
        $url  = $this->parseUrl();
        $file = 'application/controllers/' . $url[0] . '.php';

        if ( file_exists($file) ) {
            $this->_controller = $url[0];
            unset($url[0]);
        }

        include 'application/controllers/' . $this->_controller . '.php';

        $this->_controller = new $this->_controller;

        if ( isset($url[1]) ) {
            if ( method_exists($this->_controller, $url[1]) ) {
                $this->_method = $url[1];
                unset($url[1]);
            }
        }

        $this->_params = $url ? array_values($url) : array();

        call_user_func_array( array($this->_controller, $this->_method), array($this->_params));
    }

    public function parseURL() {
        if ( isset($_GET['url']) ) {
            return $url = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL) );
        }
    }
}