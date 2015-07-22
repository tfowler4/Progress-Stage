<?php
class Post {
    public static function get($key) {
        $value = '';

        if ( isset($_POST[$key]) ) {
            $value = trim($_POST[$key]);
        } elseif ( isset($_FILES[$key]) ) {
            $value = $_FILES[$key];
        }

        return $value;
    }

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

    public static function formActive() {
        if ( isset($_POST['active']) ) {
            return true;
        }
    }
}