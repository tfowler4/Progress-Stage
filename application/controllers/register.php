<?php
class Register extends Controller {
    public function index($params) {
        $this->view('', $this->model('Register', $params));
    }
}