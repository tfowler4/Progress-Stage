<?php
class LoginModel extends Model {
    protected $_validSubmission = false;
    protected $_sucessfulSubmission = false;
    protected $_formFields;
    protected $_dialogOptions;

    const PAGE_TITLE = 'Login';

    public function __construct($module, $params) {
        parent::__construct($module);

        $this->title = self::PAGE_TITLE;

        $this->loadFormFields();

        $this->_formFields = new LoginFormFields();

        if ( Post::formActive() ) { // Form has required fields filled out
            $this->_validSubmission = $this->validateForm();

            if ( $this->_validSubmission ) { // Ensures guild does not have encounter already submitted
                $this->_sucessfulSubmission = $this->processForm();

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

    public function processForm() {
        $dbh               = DbFactory::getDbh();
        $encryptedPassword = $this->encryptPasscode($this->_formFields->password);

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

    public function validateForm() {
        $this->_formFields->username = Post::get('login-username');
        $this->_formFields->password = Post::get('login-password');

        if ( !empty($this->_formFields->username) && !empty($this->_formFields->password) ) {
            $this->_validSubmission = true;
        }

        return $this->_validSubmission;
    }

    public function encryptPasscode($password) {
        $encryptedPasscode = sha1($password);

        for ( $i = 0; $i < 5; $i++ ) {
            $encryptedPasscode = sha1($encryptedPasscode.$i);
        }

        crypt($encryptedPasscode, '');

        return $encryptedPasscode;
    }

    public function loadFormFields() {
        require 'LoginFormFields.php';
    }

    public function __destruct() {

    }
}