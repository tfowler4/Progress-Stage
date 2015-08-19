<?php
class Servers extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Servers', $params));
    }
}