<?php
abstract class DataObject {
    /**
     * magic getter
     */
    public function __get($name) {
        if ( isset($this->$name) ) {
            return $this->$name;
        }
    }

    /**
     * magic isset
     */
    public function __isset($name) {
        return isset($this->$name);
    }
}