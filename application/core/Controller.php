<?php

/**
 * base controller class
 */
class Controller {
    protected $_modelName;
    protected $_modelFile;
    protected $_contentFile;
    
    /**
     * creates requested model class
     * 
     * @param string $model  [ name of model class ]
     * @param array  $params [ parameters passed ]
     * 
     * @return object [ new model object ]
     */
    protected function _model($model, $params) {
        $this->_modelName = strtolower($model);
        $this->_modelFile = ucfirst($model) . 'Model';

        if ( SITE_ONLINE == 0 ) {
            $this->_modelName = 'error';
            $this->_modelFile = 'ErrorModel';
            $params           = 'down';
        }

        // include all class files of model
        foreach ( glob('application/models/' . $this->_modelFile . '/class/*.php') as $fileName ) {
            include $fileName;
        }

        return new $this->_modelFile($model, $params);
    }

    /**
     * creates a view of the requested model class
     * 
     * @param  string $view [ name of specific view file to use ]
     * @param  object $data [ returned model object ]
     * 
     * @return void
     */
    protected function _view($view = '', $data = array() ) {
        $jsFile             = '';
        $this->_contentFile = ABS_FOLD_TEMPLATES . $_SESSION['template'] . '/404.html';

        // Specific html file
        if ( isset($data->subModule) ) {
            $this->_contentFile = 'application/models/' . $this->_modelFile . '/html/' . $data->subModule . '.html';
        } else {
            $this->_contentFile = 'application/models/' . $this->_modelFile . '/html/index.html';
        }

        // if no content file found, display the 404 page
        if ( !file_exists($this->_contentFile) ) {
            $this->_contentFile = ABS_FOLD_TEMPLATES . $_SESSION['template'] . '/404.html';
        }

        // if specific css file found, include it
        if ( file_exists('public/css/' . $this->_modelName . '/' . $this->_modelName . '.css') ) {
            $cssFile = 'public/css/' . $this->_modelName . '/' . $this->_modelName . '.css';
        }

        // if specific js file found, include it
        if ( file_exists('public/js/' . $this->_modelName . '/' . $this->_modelName . '.js') ) {
            $jsFile = 'public/js/' . $this->_modelName . '/' . $this->_modelName . '.js';
        }

        // include the index html file
        include ABS_FOLD_TEMPLATES . $_SESSION['template'] . '/index.html';
    }
}