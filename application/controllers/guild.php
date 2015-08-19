<?php
class Guild extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Guild', $params));
    }
}