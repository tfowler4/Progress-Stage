<?php
class ContactUs extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('ContactUs', $params));
    }
}