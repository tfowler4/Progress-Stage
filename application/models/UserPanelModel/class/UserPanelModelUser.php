<?php

/**
 * user control panal page for user administration
 */
class UserPanelModelUser extends UserPanelModel {
    protected $_action;
    protected $_formFields;
    protected $_dialogOptions;
    protected $_userDetails;

    const USER_EMAIL    = 'email';
    const USER_PASSWORD = 'password';

    /**
     * constructor
     */
    public function __construct($action, $formFields, $userDetails) {
        $this->_userDetails = $userDetails;
        $this->_action      = $action;
        $this->_formFields  = $formFields;

        if ( Post::formActive() ) {
            $this->_populateFormFields();

            switch ( $this->_action ) {
                case self::USER_EMAIL:
                    FormValidator::validate('user-email', $this->_formFields);
                    break;
                case self::USER_PASSWORD:
                    FormValidator::validate('user-password', $this->_formFields);
                    break;
            }

            if ( FormValidator::$isFormInvalid ) {
                $this->_dialogOptions = array('title' => 'Error', 'message' => FormValidator::$message);
                return;
            }

            switch ( $this->_action ) {
                case self::USER_EMAIL:
                    $this->_updateEmail();
                    break;
                case self::USER_PASSWORD:
                    $this->_updatePassword();
                    break;
            }
        }
    }

    /**
     * process submitted user form
     * 
     * @return void
     */
    private function _populateFormFields() {
        $this->_formFields->userId            = Post::get('userpanel-user-id');
        $this->_formFields->email             = Post::get('userpanel-email');
        $this->_formFields->oldPassword       = Post::get('userpanel-password');
        $this->_formFields->newPassword       = Post::get('userpanel-new-password');
        $this->_formFields->retypeNewPassword = Post::get('userpanel-new-retype-password');
    }

    /**
     * update user email in database
     * 
     * @return void
     */
    private function _updateEmail() {
        DbObjects::editUserEmail($this->_formFields);

        $this->_userDetails = $this->_getUpdatedUserDetails($this->_userDetails->_userId);

        $this->_dialogOptions = array('title' => 'Success', 'message' => 'You have successfully updated your email address!');
    }

    /**
     * update user password in database
     * 
     * @return void
     */
    private function _updatePassword() {
        $this->_formFields->newPassword = FormValidator::encryptPasscode($this->_formFields->newPassword);

        DbObjects::editUserPassword($this->_formFields);

        $this->_dialogOptions = array('title' => 'Success', 'message' => 'You have successfully updated your password!');
    }
}