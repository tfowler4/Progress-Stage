<?php
class Login extends Controller {
    public function index($params) {
        $this->view('', $this->model('Login', $params));
    }
}