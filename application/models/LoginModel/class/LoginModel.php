<?php

/**
 * login to the website page
 */
class LoginModel extends Model {
    protected $_validSubmission = false;
    protected $_sucessfulSubmission = false;
    protected $_formFields;
    protected $_dialogOptions;

    const PAGE_TITLE = 'Login';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_loadFormFields();

        $this->_formFields = new LoginFormFields();

        if ( Post::formActive() ) { // Form has required fields filled out
            $this->_validSubmission = $this->_validateForm();

            if ( $this->_validSubmission ) { // Ensures guild does not have encounter already submitted
                $this->_sucessfulSubmission = $this->_processForm();

                if ( $this->_sucessfulSubmission ) { // If successful login, redirect
                    $pathToCP = HOST_NAME . '/userpanel';
                    header('Location: ' . $pathToCP);
                }
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'Invalid Username/Password Combo! Please try again!');
            }
        } else {
            $this->_dialogOptions = array('title' => 'Error', 'message' => 'Empty forms are no good! Please try again!');
        }
    }

    /**
     * process submitted contact us form
     * 
     * @return boolean [ true if email was sent successfully ]
     */
    private function _processForm() {
        $dbh               = DbFactory::getDbh();
        $encryptedPassword = $this->_encryptPasscode($this->_formFields->password);

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE username='%s'
                AND passcode='%s'",
             DbFactory::TABLE_USERS,
             $this->_formFields->username,
             $encryptedPassword
             ));
        $query->execute();

        while ( $user = $query->fetch(PDO::FETCH_ASSOC) ) {
            $_SESSION['userId']      = $dbh->lastInsertId('user_id');
            $_SESSION['logged']      = 'yes';
            $_SESSION['userDetails'] = new User($user);
            $this->_sucessfulSubmission   = true;
        }

        return $this->_sucessfulSubmission;
    }

    /**
     * validate submitted contact us form for invalid submission
     * 
     * @return boolean [ true if submission is valid ]
     */
    private function _validateForm() {
        $this->_formFields->username = Post::get('login-username');
        $this->_formFields->password = Post::get('login-password');

        if ( !empty($this->_formFields->username) && !empty($this->_formFields->password) ) {
            $this->_validSubmission = true;
        }

        return $this->_validSubmission;
    }

    /**
     * encrypt the submitted password
     * 
     * @param  string $password [ unencrypted password ]
     * 
     * @return string [ encrypted password ]
     */
    private function _encryptPasscode($password) {
        $encryptedPasscode = sha1($password);

        for ( $i = 0; $i < 5; $i++ ) {
            $encryptedPasscode = sha1($encryptedPasscode.$i);
        }

        crypt($encryptedPasscode, '');

        return $encryptedPasscode;
    }

    /**
     * load form fields object
     * 
     * @return void
     */
    private function _loadFormFields() {
        require 'LoginFormFields.php';
    }
}