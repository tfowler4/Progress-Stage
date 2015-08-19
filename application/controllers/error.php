<?php
class Error extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Error', $params));
    }
}