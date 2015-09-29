<?php

/**
 * login to the website page
 */
class LoginModel extends Model {
    protected $_formFields;
    protected $_dialogOptions;

    const PAGE_TITLE = 'Login';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_formFields = new LoginFormFields();

        // submit form if one is active
        if ( Post::formActive() ) {
            $this->_populateFormFields();

            FormValidator::validate('login', $this->_formFields);

            if ( FormValidator::$isFormInvalid ) {
                $this->_dialogOptions = array('title' => 'Error', 'message' => FormValidator::$message);
                return;
            }

            $this->_processLogin();

            $pathToCP = HOST_NAME . '/userpanel';
            header('Location: ' . $pathToCP);
            die();
        }
    }

    /**
     * populate form fields object with form values
     * 
     * @return void
     */
    private function _populateFormFields() {
        $this->_formFields->username = Post::get('login-username');
        $this->_formFields->password = Post::get('login-password');
    }

    /**
     * process submitted login form
     * 
     */
    private function _processLogin() {
        $dbh               = DbFactory::getDbh();
        $encryptedPassword = FormValidator::encryptPasscode($this->_formFields->password);
        $loginSelector     = 'username';

        // check if the user is using an email and change loginSelector
        if ( filter_var($this->_formFields->username, FILTER_VALIDATE_EMAIL) !== false ) {
            $loginSelector = 'email';
        }

        $query = $dbh->prepare(sprintf(
            "SELECT user_id,
                    username,
                    email,
                    passcode,
                    active,
                    date_joined,
                    confirmcode,
                    admin
               FROM %s
              WHERE %s='%s'
                AND passcode='%s'",
             DbFactory::TABLE_USERS,
             $loginSelector,
             $this->_formFields->username,
             $encryptedPassword
             ));
        $query->execute();

        while ( $user = $query->fetch(PDO::FETCH_ASSOC) ) {
            $_SESSION['userId']      = $dbh->lastInsertId('user_id');
            $_SESSION['logged']      = 'yes';
            $_SESSION['userDetails'] = new User($user);
        }
    }
}