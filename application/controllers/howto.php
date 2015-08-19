<?php
class Howto extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Howto', $params));
    }
}