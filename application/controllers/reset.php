<?php
class Reset extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Reset', $params));
    }
}