<?php
class QuickSubmission extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('QuickSubmission', $params));
    }
}