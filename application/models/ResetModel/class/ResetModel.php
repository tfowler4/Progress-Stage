<?php

/**
 * reset password for user account page
 */
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

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        if ( !empty($params) && $params[0] == self::SUB_COMPLETE ) { // complete submodule, with confirm code and retype passwords in
            $this->_formFields                    = new ConfirmFormFields();
            $this->_formFields->newPassword       = Post::get('reset-password');
            $this->_formFields->retypeNewPassword = Post::get('reset-password-confirm');
            $this->_formFields->confirmCode       = Post::get('reset-confirm-code');

            $this->_userDetails = $this->_findConfirmCode($this->_formFields->confirmCode);

            if ( $this->_userDetails ) {
                if ( $this->_validatePassword() ) {
                    $this->_encryptedPassword = $this->_encryptPasscode($this->_formFields->newPassword);
                    $this->_updateUserPassword();

                    $this->_dialogOptions = array('title' => 'Notice', 'message' => 'Your password has been reset! Please try logging in now!');
                    return;
                }
            }
        } elseif ( !empty($params) && count($params) == 1 ) { // Only confirmcode exists
            $this->_confirmCode = $params[0];
            $this->_userDetails = $this->_findConfirmCode($this->_confirmCode);

            if ( $this->_userDetails ) {
                $this->_action ='complete';
            }
        } else {
            $this->_formFields = new ResetFormFields();
            
            if ( Post::formActive() ) { // Form has required fields filled out
                $this->_validSubmission = $this->_validateForm();
            } else {
                return;
            }

            if ( $this->_validSubmission ) {
                $this->_userDetails = $this->_searchForUser();
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'There was an odd error with your form, please');
                return;
            }

            if ( $this->_userDetails ) {
                $this->_setConfirmCode();
                $this->_successfulEmail = $this->_sendResetEmail();
                $this->_dialogOptions = array('title' => 'Notice', 'message' => 'An email has been sent to the user account\'s email address containing the reset link.');
                return;
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'We are unable to locate your user');
                return;
            }
        }
    }

    /**
     * query the database for the submitted confirmation code
     * 
     * @param  string $confirmCode [ confirmation code to reset password ]
     * 
     * @return User [ user data object ]
     */
    private function _findConfirmCode($confirmCode) {
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

    /**
     * set confirmation code to user record in database
     *
     * @return void
     */
    private function _setConfirmCode() {
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

    /**
     * send email containing confirmation link to reset password
     * 
     * @return void
     */
    private function _sendResetEmail() {
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

    /**
     * query database for user based on submitted form fields
     * 
     * @return mixed [ user data object if found else null ]
     */
    private function _searchForUser() {
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

    /**
     * update user password in database
     * 
     * @return void
     */
    private function _updateUserPassword() {
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
     * validate if submitted passwords match
     * 
     * @return boolean [ true if password matches ]
     */
    private function _validatePassword() {
        if ( ($this->_formFields->newPassword == $this->_formFields->retypeNewPassword)
             && strlen($this->_formFields->newPassword) >= PASSWORD_MINIMUM) {
            return true;
        }

        return false;
    }

    /**
     * validate submitted reset form for invalid submission
     * 
     * @return boolean [ true if submission is valid ]
     */
    private function _validateForm() {
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
}