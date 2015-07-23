<?php
class UserPanel extends Controller {
    public function index($params) {
        $this->view('', $this->model('UserPanel', $params));
    }
}