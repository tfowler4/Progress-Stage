<?php
class Servers extends Controller {
    public function index($params) {
        $this->view('', $this->model('Servers', $params));
    }
}