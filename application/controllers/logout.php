<?php
class Logout extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Logout', $params));
    }
}