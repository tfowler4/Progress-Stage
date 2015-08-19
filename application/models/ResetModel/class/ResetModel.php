<?php
class ResetModel extends Model {
    protected $_validSubmission = false;
    protected $_successfulEmail = false;
    protected $_formFields;
    protected $_searchByValue;
    protected $_userDetails;
    protected $_confirmCode;
    protected $_dialogOptions;
    protected $_action;
    protected $_encryptedPassword;

    const SUB_COMPLETE = 'complete';

    const PAGE_TITLE = 'Password Recovery';

    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->loadFormFields();

        if ( !empty($params) && $params[0] == self::SUB_COMPLETE ) { // complete submodule, with confirm code and retype passwords in
            $this->_formFields                    = new ConfirmFormFields();
            $this->_formFields->newPassword       = Post::get('reset-password');
            $this->_formFields->retypeNewPassword = Post::get('reset-password-confirm');
            $this->_formFields->confirmCode       = Post::get('reset-confirm-code');

            $this->_userDetails = $this->findConfirmCode($this->_formFields->confirmCode);

            if ( $this->_userDetails ) {
                if ( $this->validatePassword() ) {
                    $this->_encryptedPassword = $this->encryptPasscode($this->_userDetails->_userName, $this->_formFields->newPassword);
                    $this->updateUserPassword();

                    $this->_dialogOptions = array('title' => 'Notice', 'message' => 'Your password has been reset! Please try logging in now!');
                    return;
                }
            }
        } elseif ( !empty($params) && count($params) == 1 ) { // Only confirmcode exists
            $this->_confirmCode = $params[0];
            $this->_userDetails = $this->findConfirmCode($this->_confirmCode);

            if ( $this->_userDetails ) {
                $this->_action ='complete';
            }
        } else {
            $this->_formFields = new ResetFormFields();
            
            if ( Post::formActive() ) { // Form has required fields filled out
                $this->_validSubmission = $this->validateForm();
            } else {
                return;
            }

            if ( $this->_validSubmission ) {
                $this->_userDetails = $this->searchForUser();
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'There was an odd error with your form, please');
                return;
            }

            if ( $this->_userDetails ) {
                $this->setConfirmCode();
                $this->_successfulEmail = $this->sendResetEmail();
                $this->_dialogOptions = array('title' => 'Notice', 'message' => 'An email has been sent to the user account\'s email address containing the reset link.');
                return;
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'We are unable to locate your user');
                return;
            }
        }
    }

    public function findConfirmCode($confirmCode) {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE confirmcode='%s'",
             DbFactory::TABLE_USERS,
             $confirmCode
             ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            return new User($row);
        }
    }

    public function setConfirmCode() {
        $dbh = DbFactory::getDbh();

        $this->_confirmCode = md5(uniqid(rand(), true));

        $query = $dbh->prepare(sprintf(
            "UPDATE %s
                SET confirmcode ='%s'
              WHERE user_id='%s'",
             DbFactory::TABLE_USERS,
             $this->_confirmCode,
             $this->_userDetails->_userId
             ));
        $query->execute();
    }

    public function sendResetEmail() {
        $emailHeader   = array();
        $emailHeader[] = 'From: ' . EMAIL_ADMIN;
        $emailHeader[] = 'Reply-To:';
        $emailHeader[] = 'Return-Path:';
        $emailHeader[] = 'CC:';
        $emailHeader[] = 'BCC:';
        $emailSubject  = SITE_TITLE_SHORT . ' - Password Recovery';

        $userId       = $this->_userDetails->_userId;
        $emailAddress = $this->_userDetails->_emailAddress;
        $emailLink    = HOST_NAME . '/reset/' . $this->_confirmCode;
        $emailMessage = 'Dear Registered User,<br><br>In order to reset your password, please click the following link to complete your password recovery process!<br><br>' . $emailLink;
        
        Functions::sendMail($emailAddress, $emailSubject, $emailMessage, $emailHeader);
    }

    public function searchForUser() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE %s='%s'",
             DbFactory::TABLE_USERS,
             $this->_searchByValue,
             $this->_formFields->{$this->_searchByValue}
             ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            return new User($row);
        }

        return null;
    }

    public function updateUserPassword() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "UPDATE %s
                SET passcode = '%s',
                    confirmCode = ''
              WHERE user_id = '%s'",
            DbFactory::TABLE_USERS,
            $this->_encryptedPassword,
            $this->_userDetails->_userId
            ));
        $query->execute();
    }

    public function encryptPasscode($username, $password) {
        $encryptedPasscode = sha1($password);

        for ( $i = 0; $i < 5; $i++ ) {
            $encryptedPasscode = sha1($encryptedPasscode.$i);
        }

        crypt($encryptedPasscode, '');

        return $encryptedPasscode;
    }

    public function validatePassword() {
            if ( ($this->_formFields->newPassword == $this->_formFields->retypeNewPassword)
                 && strlen($this->_formFields->newPassword) >= PASSWORD_MINIMUM) {
                return true;
            }

        return false;
    }

    public function validateForm() {
        $this->_formFields->username = Post::get('reset-username');
        $this->_formFields->email    = Post::get('reset-email');

        if ( !empty($this->_formFields->email) ) {
            $this->_searchByValue = 'email';
            $this->_validSubmission = true;
        } elseif ( !empty($this->_formFields->username) ) {
            $this->_searchByValue = 'username';
            $this->_validSubmission = true;
        }

        return $this->_validSubmission;
    }

    public function loadFormFields() {
        require 'ResetFormFields.php';
    }

    public function getHTML($data) {
        $htmlFile = $this->subModule . '-' . $this->_action . '.html';
        include ABSOLUTE_PATH . '/application/models/UserPanelModel/html/' . $htmlFile;
    }

    public function __destruct() {

    }
}