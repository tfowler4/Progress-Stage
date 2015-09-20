<?php
class UserPanelModelUser extends UserPanelModel {
    protected $_userDetails;

    const USER_EMAIL      = 'email';
    const USER_PASSWORD   = 'password';

    public function __construct($userDetails) {
        $this->_userDetails = $userDetails;

        if ( Post::formActive() ) {
            $this->_processUserForm();

            if ( $this->_validForm ) {
                switch($this->_action) {
                    case self::USER_EMAIL:
                        $this->_updateEmail();
                        break;
                    case self::USER_PASSWORD:
                        if ( $this->_validatePassword() ) {
                            $this->_updatePassword();
                        } else {
                            $this->_dialogOptions = array('title' => 'Error', 'message' => 'Ensure your old password is correct and new password is typed correctly.');
                        }
                        
                        break;
                }

                header('Location: ' . $pathToCP);
            }
        }
    }

    /**
     * process submitted user form
     * 
     * @return void
     */
    private function _processUserForm() {
        $this->_formFields->userId            = Post::get('userpanel-user-id');
        $this->_formFields->email             = Post::get('userpanel-email');
        $this->_formFields->oldPassword       = Post::get('userpanel-password');
        $this->_formFields->newPassword       = Post::get('userpanel-new-password');
        $this->_formFields->retypeNewPassword = Post::get('userpanel-new-retype-password');

        if ( $this->_action == self::USER_EMAIL ) {
            if ( !empty($this->_formFields->email) ) {
                $this->_validForm = true;
            }
        } else if ( $this->_action == self::USER_PASSWORD ) {
            if ( !empty($this->_formFields->oldPassword) 
                 && !empty($this->_formFields->newPassword) 
                 && !empty($this->_formFields->retypeNewPassword) ) {
                $this->_validForm = true;
            }
        }
    }

    /**
     * update user email in database
     * 
     * @return void
     */
    private function _updateEmail() {
        DBObjects::editUserEmail($this->_formFields);
    }

    /**
     * update user password in database
     * 
     * @return void
     */
    private function _updatePassword() {
        $this->_formFields->newPassword = $this->_encryptPasscode($this->_formFields->newPassword);

        DBObjects::editUserPassword($this->_formFields);
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
     * validate that both passwords match
     * 
     * @return boolean [ true if passwords match ]
     */
    private function _validatePassword() {
        if ( $this->_encryptPasscode($this->_formFields->oldPassword) == $this->_userDetails->_encrypedPassword ) {
            if ( ($this->_formFields->newPassword == $this->_formFields->retypeNewPassword)
                 && strlen($this->_formFields->newPassword) >= PASSWORD_MINIMUM) {
                return true;
            }
        }

        return false;
    }
}