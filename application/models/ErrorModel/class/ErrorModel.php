<?php

/**
 * error page when missing pages occur
 */
class ErrorModel extends Model {
    const PAGE_TITLE = 'Woops 404!';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;
    }
}