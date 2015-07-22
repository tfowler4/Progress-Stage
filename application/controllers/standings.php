<?php
class Standings extends Controller {
    public function index($params) {
        $this->view('', $this->model('Standings', $params));
    }
}