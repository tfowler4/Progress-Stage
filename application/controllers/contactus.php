<?php
class ContactUs extends Controller {
    public function index($params) {
        $this->view('', $this->model('ContactUs', $params));
    }
}