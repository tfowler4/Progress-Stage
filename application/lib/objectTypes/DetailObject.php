<?php
abstract class DetailObject {
    /**
     * get all the properties of the object
     * 
     * @return array [ all properties ]
     */
    public function getProperties() {
        return get_object_vars($this);
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