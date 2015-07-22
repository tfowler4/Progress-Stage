<?php
class ResetModel extends Model {
    protected $_validSubmission = false;
    protected $_successfulEmail = false;
    protected $_formFields;
    protected $_searchByValue;
    protected $_userDetails;
    protected $_confirmCode;
    protected $_dialogOptions;

    const PAGE_TITLE = 'Password Recovery';

    public function __construct($module, $params) {
        parent::__construct($module);

        $this->title = self::PAGE_TITLE;

        if ( isset($params) && count($params) > 0 ) {
            $this->_userDetails = $this->findConfirmCode($params[0]);

            if ( $this->_userDetails ) {
                
            }
        }

        $this->loadFormFields();

        $this->_formFields = new ResetFormFields();
        /*
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
            return;
        } else {
            $this->_dialogOptions = array('title' => 'Error', 'message' => 'We are unable to locate your user');
            return;
        }
        */
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

        if ( $dbh->lastInsertId('user_id') !== 0 ) {
            $_SESSION['userId']      = $dbh->lastInsertId('user_id');
            $_SESSION['logged']      = 'yes';
            $_SESSION['userDetails'] = new User($query->fetch(PDO::FETCH_ASSOC));
            $this->_sucessfulLogin   = true;
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

    public function __destruct() {

    }
}