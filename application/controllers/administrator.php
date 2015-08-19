<?php
class Administrator extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Administrator', $params));
    }
}