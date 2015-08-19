<?php
class UserPanel extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('UserPanel', $params));
    }
}