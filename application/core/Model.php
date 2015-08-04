<?php
abstract class Model {
    protected $_title;
    protected $_description;
    protected $_blockTitle;
    protected $_primaryContent = array();
    protected $_blockContent = array();
    protected $_db;
    protected $_module;
    protected $_settings;

    public function __construct($module) {
        $this->_module      = strtolower($module);
        $this->_settings    = ( isset($GLOBALS[$this->_module]) ? $GLOBALS[$this->_module] : '');
        $this->_title       = ( isset($this->_settings['title']) ? $this->_settings['title'] : '');
        $this->_description = ( isset($this->_settings['description']) ? $this->_settings['description'] : '');

        if ( Post::get('request') == 'spreadsheet' ) {
            $html = Template::getSpreadsheet(Post::get('dungeon'));
            echo $html;
            die;
        } elseif ( Post::get('request') == 'form' ) {
            $html = Template::getPopupForm(Post::get('formId'));
            echo $html;
            die;
        } elseif ( Post::get('request') == 'search' ) {
            $html = Template::getPopupForm(Post::get('formId'));
            echo $html;
            die;
        }
    }
    
    public function __get($name) {
        if ( isset($this->$name) ) {
            return $this->$name;
        }
    }
    
    public function __set($name, $value) {
        $this->$name = $value;
    }
    
    public function __isset($name) {
        return isset($this->$name);
    }
}