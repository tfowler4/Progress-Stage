<?php
class Guild extends Controller {
    public function index($params) {
        $this->view('', $this->model('Guild', $params));
    }
}