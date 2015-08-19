<?php
class Login extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Login', $params));
    }
}