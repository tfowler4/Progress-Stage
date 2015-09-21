<?php
class UserPanelModelKill extends UserPanelModel {
    protected $_action;
    protected $_formFields;
    protected $_dialogOptions;
    protected $_guildDetails;
    protected $_encounterDetails;
    protected $_encounterScreenshot;

    const KILLS_ADD       = 'add';
    const KILLS_REMOVE    = 'remove';
    const KILLS_EDIT      = 'edit';

    const TABLE_HEADER_PROGRESSION = array(
            'Encounter'      => '_encounterName',
            'Date Completed' => '_datetime',
            'Server'         => '_serverLink',
            'WR'             => '_worldRankImage',
            'RR'             => '_regionRankImage',
            'SR'             => '_serverRankImage',
            'Kill Video'     => '_videoLink',
            'Screenshot'     => '_screenshotLink',
            'Options'        => '_options'
        );

    public function __construct($action, $formFields, $guildDetails) {
        $this->_guildDetails = $guildDetails;
        $this->_action       = $action;
        $this->_formFields   = $formFields;

        $this->mergeOptionsToEncounters();

        if ( Post::formActive() ) {
            $this->_processKillForm();

            if ( $this->_validForm ) {
                switch($this->_action) {
                    case self::KILLS_ADD:
                        $this->_addKill();
                        break;
                    case self::KILLS_REMOVE:
                        $this->_removeKill();
                        break;
                    case self::KILLS_EDIT:
                        $this->_editKill();
                        break;
                }

                //header('Location: ' . $pathToCP);
            }
        }
    }

    /**
     * process submitted kill submitted form
     * 
     * @return void
     */
    private function _processKillForm() {
        $this->_formFields->guildId    = Post::get('userpanel-guild');
        $this->_formFields->encounter  = Post::get('userpanel-encounter');
        $this->_formFields->dateMonth  = Post::get('userpanel-month');
        $this->_formFields->dateDay    = Post::get('userpanel-day');
        $this->_formFields->dateYear   = Post::get('userpanel-year');
        $this->_formFields->dateHour   = Post::get('userpanel-hour');
        $this->_formFields->dateMinute = Post::get('userpanel-minute');
        $this->_formFields->screenshot = Post::get('userpanel-screenshot');
        $this->_formFields->video      = Post::get('userpanel-video');
        $this->_formFields->videoId    = Post::get('video-link-id');
        $this->_formFields->videoTitle = Post::get('video-link-title');
        $this->_formFields->videoUrl   = Post::get('video-link-url');
        $this->_formFields->videoType  = Post::get('video-link-type');

        if ( !empty($this->_formFields->guildId)
             && !empty($this->_formFields->encounter) 
             && !empty($this->_formFields->dateMonth) 
             && !empty($this->_formFields->dateDay) 
             && !empty($this->_formFields->dateYear) 
             && !empty($this->_formFields->dateHour) 
             && !empty($this->_formFields->dateMinute) 
             && !empty($this->_formFields->screenshot) ) {
                $this->_validForm = true;
        }

        if ( $this->_action == self::KILLS_REMOVE ) {
            if ( !empty($this->_formFields->guildId)
                 && !empty($this->_formFields->encounter ) ) {
                    $this->_validForm = true;
            }
        }
    }

    /**
     * remove kill from guild progression string in database
     * 
     * @return void
     */
    private function _removeKill() {
        $progressionString = $this->_removeKillFromProgressionString($this->_guildDetails->_progression);

        DBObjects::removeKill($this->_formFields, $progressionString);
        $this->_removeScreenshot($this->_formFields->guildId, $this->_formFields->encounter);
    }

    /**
     * edit kill from guild progression in database
     * 
     * @return void
     */
    private function _editKill() {
        $progressionString = $this->_removeKillFromProgressionString($this->_guildDetails->_progression);
        $progressionString = $this->_generateProgressionString($progressionString);

        DBObjects::editKill($this->_formFields, $progressionString);

        if ( Functions::validateImage($this->_formFields->screenshot) ) {
            $imagePath = ABS_FOLD_KILLSHOTS . $this->_formFields->guildId . '-' . $this->_formFields->encounter;

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }

            move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);
        }
    }

    /**
     * add kill from guild progression in database
     * 
     * @return void
     */
    private function _addKill() {
        $progressionString = $this->_generateProgressionString($this->_guildDetails->_progression);

        DBObjects::addKill($this->_formFields, $progressionString);

        if ( Functions::validateImage($this->_formFields->screenshot) ) {
            $imagePath = ABS_FOLD_KILLSHOTS . $this->_formFields->guildId . '-' . $this->_formFields->encounter;

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }

            move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);
        }
    }

    /**
     * remove screenshot image from filesystem
     * 
     * @return void
     */
    private function _removeScreenshot($guildId, $encounterId) {
        $imagePath = ABS_FOLD_KILLSHOTS . $guildId . '-' . $encounterId;

        if ( file_exists($imagePath) ) {
            unlink($imagePath);
        }
    }

    /**
     * remove kill from guild progression string
     *
     * @param  string $progressionString [ kill progression string ]
     * 
     * @return void
     */
    private function _removeKillFromProgressionString($progressionString) {
        $newProgressionString = '';

        if ( !empty($progressionString) ) {
            $progressionArray = explode("~~", $progressionString);

            $numOfProgression = count($progressionArray);
            for ( $count = 0; $count < $numOfProgression; $count++ ) {
                $progressionDetails = explode("||", $progressionArray[$count]);
                $encounterId        = $progressionDetails[0];

                if ( $encounterId == $this->_formFields->encounter ) {
                    unset($progressionArray[$count]);

                    $newProgressionString = implode("~~", $progressionArray);
                    break;
                }
            }
        }

        return $newProgressionString;
    }

    /**
     * generate database upload string for progression column in guild table
     *
     * @param  string $progressionString [ kill progression string ]
     * 
     * @return string [ progression string ]
     */
    private function _generateProgressionString($progressionString) {
        $insertString = Functions::generateDBInsertString($progressionString, $this->_formFields, $this->_guildDetails->_guildId);

        return $insertString;
    }

    /**
     * adds edit/remove option properties to encounter details object
     * 
     * @return void
     */
    private function mergeOptionsToEncounters() {
        foreach( $this->_guildDetails->_encounterDetails as $encounterId => $encounterDetails ) {
            $newEncounterDetails = new stdClass();

            $encounterProperties = $encounterDetails->getProperties();

            foreach ( $encounterProperties as $key => $value ) {
                $newEncounterDetails->$key = $value;
            }

            $optionsString = '';
            $optionsString .= $this->generateInternalHyperlink(UserPanelModel::SUB_KILLS, UserPanelModelKill::KILLS_EDIT . '/' . $this->_guildDetails->_guildId .'/' . $encounterId, 'Edit', true);
            $optionsString .= ' | ';
            $optionsString .= $this->generateInternalHyperlink(UserPanelModel::SUB_KILLS, UserPanelModelKill::KILLS_REMOVE . '/' . $this->_guildDetails->_guildId . '/' . $encounterId, 'Delete', true);

            $newEncounterDetails->_options = $optionsString;

            $this->_guildDetails->_encounterDetails->$encounterId = $newEncounterDetails;
        }
    }
}