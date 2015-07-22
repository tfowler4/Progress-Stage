<?php
class Error extends Controller {
    public function index($params) {
        $this->view('', $this->model('Error', $params));
    }
}