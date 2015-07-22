<?php
class PrivacyPolicy extends Controller {
    public function index($params) {
        $this->view('', $this->model('PrivacyPolicy', $params));
    }
}