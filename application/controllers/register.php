<?php
class Register extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Register', $params));
    }
}