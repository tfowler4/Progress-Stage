<?php

class Administrator extends Controller {
    public function index($params) {
        $this->view('', $this->model('Administrator', $params));
    }
}