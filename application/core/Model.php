<?php

/**
 * base model class
 */
abstract class Model {
    public $title;
    public $description;

    /**
     * constructor
     */
    public function __construct() {
        switch(Post::get('request')) {
            case 'form':
            case 'search':
                $html = Template::getPopupForm(Post::get('formId'));
                echo $html;
                die;
                break;
            case 'spreadsheet':
                $html = Template::getSpreadsheet(Post::get('dungeon'));
                echo $html;
                die;
                break;
            case 'encounterList':
                $html = Template::getEncounterDropdownListHtml(Post::get('guild'));
                echo $html;
                die;
                break;
            case 'videoList':
                $html = Template::getVideoListHtml(Post::get('guild'), Post::get('encounter'));
                echo $html;
                die;
                break;
        }

        /*
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
        } elseif ( Post::get('request') == 'video' ) {
            $html = Template::getPopupForm(Post::get('formId'));
            echo $html;
            die;
        }  elseif ( Post::get('request') == 'encounterList' ) {
            $html = Template::getEncounterDropdownListHtml(Post::get('guild'));
            echo $html;
            die;
        }
        */
    }

    /**
     * magic getter
     */
    public function __get($name) {
        if ( isset($this->$name) ) {
            return $this->$name;
        }
    }

    /**
     * magic setter
     */
    public function __set($name, $value) {
        $this->$name = $value;
    }

    /**
     * magic isset
     */
    public function __isset($name) {
        return isset($this->$name);
    }

    /**
     * magic destruct
     */
    public function __destruct() {}

    /**
     * magic unset
     */
    public function __unset($name) {
        unset($this->$name);
    }
}