<?php

/**
 * privacy policy article page
 */
class PrivacyPolicyModel extends Model {
    const PAGE_TITLE = 'Privacy Policy';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;
    }
}