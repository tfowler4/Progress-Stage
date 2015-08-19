<?php
class ErrorModel extends Model {
    const PAGE_TITLE = 'Woops 404!';

    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;
    }
}