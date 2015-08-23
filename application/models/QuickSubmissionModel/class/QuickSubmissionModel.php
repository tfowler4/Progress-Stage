<?php

/**
 * submit kills without logging in page
 */
class QuickSubmissionModel extends Model {
    protected $_validSubmission = false;
    protected $_validEncounter = false;
    protected $_validScreenshot = false;
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

        if ( Post::formActive() ) { // Form has required fields filled out
            $this->_validSubmission = $this->validateForm();

            if ( $this->_validSubmission ) { // Ensures guild does not have encounter already submitted
                $this->_encounterExists = $this->validateEncounter();

                if ( $this->_encounterExists ) { // Ensures a valid screenshot is submitted
                    $this->_validScreenshot = Functions::validateImage($this->_formFields->screenshot);

                    if ( $this->_validScreenshot ) { // Submit the data
                        $this->processForm();

                        $this->_dialogOptions = array('title' => 'Success', 'message' => 'You kill has been submitted successfully! Standings and Rankings will be updated accordingly!');
                    } else {
                        $this->_dialogOptions = array('title' => 'Error', 'message' => 'All kill submissions require a valid screenshot. Please submit your kill with one!');
                    }
                } else {
                    $this->_dialogOptions = array('title' => 'Error', 'message' => 'Please choose a valid encounter! (The encounter you selected may have already been submitted)');
                }
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'Make sure you have a valid Guild, Encounter, and Screenshot selected before proceeding!');
            }
        }
    }

    /**
     * validate submitted quick submission form for invalid submission
     * 
     * @return boolean [ true if submission is valid ]
     */
    public function validateForm() {
        $this->_formFields->guildId    = Post::get('quick-guild');
        $this->_formFields->encounter  = Post::get('quick-encounter');
        $this->_formFields->dateMonth  = Post::get('quick-month');
        $this->_formFields->dateDay    = Post::get('quick-day');
        $this->_formFields->dateYear   = Post::get('quick-year');
        $this->_formFields->dateHour   = Post::get('quick-hour');
        $this->_formFields->dateMinute = Post::get('quick-minute');
        $this->_formFields->screenshot = Post::get('quick-screenshot');
        $this->_formFields->video      = Post::get('quick-video');

        if ( !empty($this->_formFields->guildId) &&
             !empty($this->_formFields->encounter) &&
             !empty($this->_formFields->dateMonth) &&
             !empty($this->_formFields->dateDay) &&
             !empty($this->_formFields->dateYear) &&
             !empty($this->_formFields->dateHour) &&
             !empty($this->_formFields->dateMinute) &&
             !empty($this->_formFields->screenshot)
             ) {
                $this->_guildId         = $this->_formFields->guildId;
                $this->_encounterId     = $this->_formFields->encounter;
                $this->_guildDetails    = CommonDataContainer::$guildArray[$this->_guildId];
                $this->_validSubmission = true;
        }

        return $this->_validSubmission;
    }

    /**
     * validate encounter if it already exists
     * 
     * @return boolean [ true if submission is valid ]
     */
    public function validateEncounter() {
        return !isset($this->_guildDetails->_encounterDetails->{$this->_encounterId});
    }

    /**
     * process submitted quick submission form
     * 
     * @return void
     */
    public function processForm() {
        $dbh               = DbFactory::getDbh();
        $progressionString = $this->generateProgressionString($this->_guildDetails->_progression);

        DBObjects::addKill($this->_formFields, $progressionString);

        if ( Functions::validateImage($this->_formFields->screenshot) ) {
            $imagePath = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/screenshots/killshots/' . $this->_formFields->guildId . '-' . $this->_formFields->encounter);

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }

            move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);
        }
    }

    /**
     * generate guild database progression string
     * 
     * @param  string $progressionString [ current progression string ]
     * 
     * @return string [ progression insert string ]
     */
    public function generateProgressionString($progressionString) {
        $insertString = Functions::generateDBInsertString($progressionString, $this->_formFields, $this->_guildId);

        return $insertString;
    }
}