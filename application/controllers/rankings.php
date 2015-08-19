<?php
class Rankings extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('Rankings', $params));
    }
}