<?php
class Rankings extends Controller {
    public function index($params) {
        $this->view('', $this->model('Rankings', $params));
    }
}