<?php

/**
 * submit kills without logging in page
 */
class QuickSubmissionModel extends Model {
    protected $_guildId;
    protected $_encounterId;
    protected $_guildDetails;
    protected $_formFields;
    protected $_dialogOptions;

    const PAGE_TITLE = 'Quick Kill Submission';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_formFields = new QuickSubmissionFormFields();

        // submit form if one is active
        if ( Post::formActive() ) {
            $this->_populateFormFields();

            FormValidator::validate('kill-add', $this->_formFields);

            if ( FormValidator::$isFormInvalid ) {
                $this->_dialogOptions = array('title' => 'Error', 'message' => FormValidator::$message);
                return;
            }

            $this->_processKillSubmission();

            $this->_dialogOptions = array('title' => 'Success', 'message' => 'Your kill has been submitted successfully! Standings and Rankings will be updated accordingly!');
        }
    }

    /**
     * validate submitted quick submission form for invalid submission
     * 
     * @return boolean [ true if submission is valid ]
     */
    protected function _populateFormFields() {
        $this->_formFields->guildId    = Post::get('quick-guild');
        $this->_formFields->encounter  = Post::get('quick-encounter');
        $this->_formFields->dateMonth  = Post::get('quick-month');
        $this->_formFields->dateDay    = Post::get('quick-day');
        $this->_formFields->dateYear   = Post::get('quick-year');
        $this->_formFields->dateHour   = Post::get('quick-hour');
        $this->_formFields->dateMinute = Post::get('quick-minute');
        $this->_formFields->screenshot = Post::get('quick-screenshot');
        $this->_formFields->videoTitle = Post::get('video-link-title');
        $this->_formFields->videoUrl   = Post::get('video-link-url');
        $this->_formFields->videoType  = Post::get('video-link-type');
    }

    /**
     * process submitted quick submission form
     * 
     * @return void
     */
    protected function _processKillSubmission() {
        $dbh = DbFactory::getDbh();

        DBObjects::addKill($this->_formFields);

        $imagePath = strtolower(ABS_FOLD_KILLSHOTS . $this->_formFields->guildId . '-' . $this->_formFields->encounter);

        if ( file_exists($imagePath) ) {
            unlink($imagePath);
        }

        move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);
    }
}