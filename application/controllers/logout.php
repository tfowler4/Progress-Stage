<?php
class Logout extends Controller {
    public function index($params) {
        $this->view('', $this->model('Logout', $params));
    }
}