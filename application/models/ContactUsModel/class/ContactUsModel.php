<?php

/**
 * contact us page to send emails to administrators
 */
class ContactUsModel extends Model {
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

        // submit form if one is active
        if ( Post::formActive() ) {
            $this->_populateFormFields();

            FormValidator::validate('contactus', $this->_formFields);

            if ( FormValidator::$isFormInvalid ) {
                $this->_dialogOptions = array('title' => 'Error',
                                              'message' => FormValidator::$message,
                                              'type' => 'danger');
                return;
            }

            $this->_processFeedback();

            $this->_dialogOptions = array('title' => 'Success',
                                          'message' => 'We have received your feedback and will be getting in contact with you shortly!',
                                          'type' => 'success');
        }
    }

    /**
     * populate form fields object with form values
     * 
     * @return void
     */
    private function _populateFormFields() {
        $this->_formFields->email    = Post::get('contact-email');
        $this->_formFields->message  = Post::get('contact-message');
        $this->_formFields->feedback = Post::get('contact-feedback');
    }

    /**
     * process submitted contact us form
     * 
     * @return boolean [ true if email was sent successfully ]
     */
    private function _processFeedback() {
        $emailAddress = EMAIL_ADMIN;
        $emailSubject = SITE_TITLE_SHORT . ' - ' . $this->_formFields->feedback;
        $emailMessage = "Dear Site Administrator,<br><br>";
        $emailMessage .= $this->_formFields->email . " has some feedback for you!<br><br>" . $this->_formFields->message;

        Logger::log('INFO', 'Sending Feedback Email: ' . $this->_formFields->email . ' -> ' . $this->_formFields->message);

        return Functions::sendMail($emailAddress, $emailSubject, $emailMessage);
    }
}