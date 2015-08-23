<?php

/**
 * contact us page to send emails to administrators
 */
class ContactUsModel extends Model {
    protected $_validSubmission = false;
    protected $_sucessfulSubmission = false;
    protected $_formFields;
    protected $_dialogOptions;

    const PAGE_TITLE = 'Contact Us';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_formFields = new ContactUsFormFields();

        if ( Post::formActive() ) { // Form has required fields filled out
            $this->_validSubmission = $this->_validateForm();

            if ( $this->_validSubmission ) { // Ensures guild does not have encounter already submitted
                $this->_sucessfulSubmission = $this->_processForm();

                if ( $this->_sucessfulSubmission ) { // If successful email
                    $this->_dialogOptions = array('title' => 'Success', 'message' => 'Your feedback has been submitted! We will be getting in contact with you shortly!');
                }
            } else {
               $this->_dialogOptions = array('title' => 'Error', 'message' => 'Please fill out the form entirely so we can better assist with your feedback!');
            }
        }
    }

    /**
     * process submitted contact us form
     * 
     * @return boolean [ true if email was sent successfully ]
     */
    private function _processForm() {
        $emailAddress = EMAIL_ADMIN;
        $emailSubject = SITE_TITLE_SHORT . ' - ' . $this->_formFields->feedback;
        $emailMessage = "Dear Site Administrator,<br><br>";
        $emailMessage .= $this->_formFields->email . " has some feedback for you!<br><br>" . $this->_formFields->message;

        Logger::log('INFO', 'Sending Feedback Email: ' . $this->_formFields->email . ' -> ' . $this->_formFields->message);

        return Functions::sendMail($emailAddress, $emailSubject, $emailMessage);
    }

    /**
     * validate submitted contact us form for invalid submission
     * 
     * @return boolean [ true if submission is valid ]
     */
    private function _validateForm() {
        $this->_formFields->email    = Post::get('contact-email');
        $this->_formFields->message  = Post::get('contact-message');
        $this->_formFields->feedback = Post::get('contact-feedback');

        if ( !empty($this->_formFields->email) 
             && !empty($this->_formFields->message) 
             && !empty($this->_formFields->feedback) ) {
                $this->_validSubmission = true;
        }

        return $this->_validSubmission;
    }
}