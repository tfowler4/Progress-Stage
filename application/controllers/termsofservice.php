<?php
class TermsOfService extends Controller {
    public function index($params) {
        $this->view('', $this->model('TermsOfService', $params));
    }
}