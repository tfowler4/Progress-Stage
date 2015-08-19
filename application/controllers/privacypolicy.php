<?php
class PrivacyPolicy extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('PrivacyPolicy', $params));
    }
}