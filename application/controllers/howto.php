<?php
class Howto extends Controller {
    public function index($params) {
        $this->view('', $this->model('Howto', $params));
    }
}