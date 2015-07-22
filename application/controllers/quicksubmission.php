<?php
class QuickSubmission extends Controller {
    public function index($params) {
        $this->view('', $this->model('QuickSubmission', $params));
    }
}