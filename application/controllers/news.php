<?php
class News extends Controller {
    public function index($params) {
        $this->view('', $this->model('News', $params));
    }
}