<?php
class Standings extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Standings', $params));
    }
}