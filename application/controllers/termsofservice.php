<?php
class TermsOfService extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('TermsOfService', $params));
    }
}