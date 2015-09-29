<?php

/**
 * reset password for user account page
 */
class ResetModel extends Model {
    protected $_formFields;
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
            $this->_formFields = new ConfirmFormFields();

            $this->_populateFormFields();

            $this->_confirmCode = $this->_formFields->confirmCode;
            $this->_userDetails = $this->_findConfirmCode($this->_formFields->confirmCode);

            if ( $this->_userDetails ) {
                FormValidator::validate('reset-confirm', $this->_formFields);

                if ( FormValidator::$isFormInvalid ) {
                    $this->_dialogOptions = array('title' => 'Error', 'message' => FormValidator::$message);
                    $this->_action        ='complete';
                    return;
                }

                $this->_updateUserPassword();

                $this->_dialogOptions = array('title' => 'Notice', 'message' => 'Your password has been reset! Try logging in now!');
            }
        } elseif ( !empty($params) && count($params) == 1 ) { // Only confirmcode exists
            $this->_confirmCode = $params[0];
            $this->_userDetails = $this->_findConfirmCode($this->_confirmCode);

            if ( $this->_userDetails ) {
                $this->_action ='complete';
            }
        } else {
            $this->_formFields = new ResetFormFields();

            // submit form if one is active
            if ( Post::formActive() ) {
                $this->_populateFormFields();

                FormValidator::validate('reset', $this->_formFields);

                if ( FormValidator::$isFormInvalid ) {
                    $this->_dialogOptions = array('title' => 'Error', 'message' => FormValidator::$message);
                    return;
                }

                $this->_processSendingEmail();
            }
        }
    }

    /**
     * populate form fields object with form values
     * 
     * @return void
     */
    private function _populateFormFields() {
        $this->_formFields->email             = Post::get('reset-email');
        $this->_formFields->newPassword       = Post::get('reset-password');
        $this->_formFields->retypeNewPassword = Post::get('reset-password-confirm');
        $this->_formFields->confirmCode       = Post::get('reset-confirm-code');
    }

    /**
     * locate user details to send reset email message
     * 
     * @return void
     */
    private function _processSendingEmail() {
        // search for user using email
        $this->_userDetails = $this->_searchForUser();

        if ( empty($this->_userDetails) ) {
            $this->_dialogOptions = array('title' => 'Error', 'message' => 'We are unable to locate a user by that email.');
            return;
        } else {
            $this->_setConfirmCode();

            if ( $this->_sendResetEmail() ) {
                $this->_dialogOptions = array('title' => 'Notice', 'message' => 'An email has been sent to the user account\'s email address containing the reset link.');
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
            "SELECT user_id,
                    username,
                    email,
                    passcode,
                    active,
                    date_joined,
                    confirmcode,
                    admin
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
        $emailAddress = $this->_userDetails->_emailAddress;
        $emailSubject = SITE_TITLE_SHORT . ' - Password Recovery';
        $emailLink    = HOST_NAME . '/reset/' . $this->_confirmCode;
        $emailMessage = 'Dear Registered User,<br><br>In order to reset your password, please click the following link to complete your password recovery process!<br><br>' . $emailLink;

        Logger::log('INFO', 'Sending Reset Email: ' . $emailAddress . ' -> ' . $emailMessage);

        Functions::sendMail($emailAddress, $emailSubject, $emailMessage);
    }

    /**
     * query database for user based on submitted form fields
     * 
     * @return mixed [ user data object if found else null ]
     */
    private function _searchForUser() {
        $dbh = DbFactory::getDbh();

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
              WHERE email='%s'",
             DbFactory::TABLE_USERS,
             $this->_formFields->email
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
        $this->_encryptedPassword = FormValidator::encryptPasscode($this->_formFields->newPassword);
        $dbh                      = DbFactory::getDbh();

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
}