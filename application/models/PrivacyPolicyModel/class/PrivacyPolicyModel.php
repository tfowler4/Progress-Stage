<?php
class PrivacyPolicyModel extends Model {
    const PAGE_TITLE = 'Privacy Policy';

    public function __construct($module, $params) {
        parent::__construct($module);

        $this->title = self::PAGE_TITLE;
    }
}