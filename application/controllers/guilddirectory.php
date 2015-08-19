<?php
class GuildDirectory extends Controller {
    public function index($params) {
        $this->_view('', $this->_model('GuildDirectory', $params));
    }
}