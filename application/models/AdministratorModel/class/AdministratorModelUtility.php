<?php

/**
 * utility administration
 */
class AdministratorModelUtility {
    protected $_action;
    protected $_dbh;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        switch ($this->_action) {
            case "run":
                $this->runUtilityScript();
                break;
        }
    }

    /**
     * run selected utility script
     * 
     * @return void
     */
    public function runUtilityScript() {
        $scriptName = Post::get('utility-run-script-name');
        $parameter  = Post::get('utility-run-paramater');

        $command = FOLD_INDEX . 'scripts/' . $scriptName;

        if ( !empty($parameter) ) {
            $command .= '?' . $parameter;
        }

        header('Location: ' . $command);
    }
}