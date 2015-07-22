<?php
class GuildDirectory extends Controller {
    public function index($params) {
        $this->view('', $this->model('GuildDirectory', $params));
    }
}