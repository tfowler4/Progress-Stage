<?php

/**
 * static class to handle $_POST values
 */
class Post {
    /**
     * retrieve value from key
     * 
     * @param  string $key [ field key from form ]
     * 
     * @return string [ post value from key ]
     */
    public static function get($key) {
        $value = '';

        if ( isset($_POST[$key]) ) {
            $value = trim($_POST[$key]);
        } elseif ( isset($_FILES[$key]) ) {
            $value = $_FILES[$key];
        }

        return $value;
    }

    /**
     * get number of fields in POST
     * 
     * @return integer [ number of fields in POST ]
     */
    public static function count() {
        $nonEmptyFields = 0;

        foreach( $_POST as $key => $value ) {
            $fieldValue = $_POST[$key];

            if ( !empty($fieldValue) ) {
                $nonEmptyFields++;
            }
        }

        return $nonEmptyFields;
    }

    /**
     * gets the current active form from button click
     * 
     * @return boolean [ if form is active then true, else false]
     */
    public static function formActive() {
        if ( isset($_POST['active']) ) {
            return true;
        }
    }
}