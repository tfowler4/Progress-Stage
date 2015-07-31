<?php
class Controller {
    public $model;
    public $modelFile;
    public $contentFile;
    
    public function model($model, $params) {
        $this->model     = strtolower($model);
        $this->modelFile = ucfirst($model) . 'Model';

        if ( SITE_ONLINE == 0 ) {
            $this->model     = 'error';
            $this->modelFile = 'ErrorModel';
            $params = 'down';
            include 'application/models/' . $this->modelFile . '/class/'. $this->modelFile . '.php';
            return new $this->modelFile($model, $params);
            //Functions::sendTo404('down');
        }

        if ( file_exists('application/models/' . $this->modelFile . '/class/' . $this->modelFile . '.php') ) {
            include 'application/models/' . $this->modelFile . '/class/'. $this->modelFile . '.php';

            return new $this->modelFile($model, $params);
        }
    }

    public function view($view = '', $data = array() ) {
        $jsFile            = '';
        $this->contentFile = 'public/templates/default/404.html';

        // Specific html file
        if ( isset($data->subModule) ) {
            $this->contentFile = 'application/models/' . $this->modelFile . '/html/' . $data->subModule . '.html';
        } else {
            $this->contentFile = 'application/models/' . $this->modelFile . '/html/index.html';
        }

        if ( !file_exists($this->contentFile) ) {
            $this->contentFile = 'public/templates/default/404.html';
        }

        if ( file_exists('public/css/' . $this->model . '/' . $this->model . '.css') ) {
            $cssFile = 'public/css/' . $this->model . '/' . $this->model . '.css';
        }

        if ( file_exists('public/js/' . $this->model . '/' . $this->model . '.js') ) {
            $jsFile = 'public/js/' . $this->model . '/' . $this->model . '.js';
        }
        
        define('TEMPLATE', "public/templates/default/");
        include 'public/templates/default/index.html';
    }
}