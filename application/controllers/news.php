<?php
class News extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('News', $params));
    }
}