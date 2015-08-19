<?php
class TermsOfServiceModel extends Model {
    const PAGE_TITLE = 'Terms of Service';
    
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;
    }
}