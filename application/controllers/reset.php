<?php
class Reset extends Controller {
    public function index($params) {
        $this->view('', $this->model('Reset', $params));
    }
}