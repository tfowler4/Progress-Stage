<?php

/**
 * user control panal page for kills
 */
class UserPanelModelKill extends UserPanelModel {
    protected $_action;
    protected $_formFields;
    protected $_dialogOptions;
    protected $_guildDetails;
    protected $_encounterDetails;
    protected $_formStatus = 0;

    const KILLS_ADD    = 'add-kill';
    const KILLS_REMOVE = 'remove-kill';
    const KILLS_EDIT   = 'edit-kill';

    const TABLE_HEADER_PROGRESSION = array(
            array('header' => 'Encounter',      'key' => '_encounterName',   'class' => ''),
            array('header' => 'Server',         'key' => '_serverLink',      'class' => 'hidden-xs hidden-sm'),
            array('header' => 'Date Completed', 'key' => '_datetime',        'class' => 'text-center hidden-xs'),
            array('header' => 'WR',             'key' => '_worldRankImage',  'class' => 'text-center hidden-xs hidden-sm hidden-md'),
            array('header' => 'RR',             'key' => '_regionRankImage', 'class' => 'text-center hidden-xs hidden-sm hidden-md'),
            array('header' => 'SR',             'key' => '_serverRankImage', 'class' => 'text-center hidden-xs hidden-sm hidden-md'),
            array('header' => 'Kill Video',     'key' => '_videoLink',       'class' => 'text-center hidden-xs'),
            array('header' => 'Screenshot',     'key' => '_screenshotLink',  'class' => 'text-center hidden-xs'),
            array('header' => 'Options',        'key' => '_options',         'class' => 'text-center')
        );

    public function __construct($action, $formFields, $guildDetails) {
        $this->_guildDetails     = $guildDetails;
        $this->_action           = $action;
        $this->_formFields       = $formFields;

        if ( Post::get('userpanel-encounter') && isset($guildDetails->_encounterDetails->{Post::get('userpanel-encounter')}) ) {
            $this->_encounterDetails = $guildDetails->_encounterDetails->{Post::get('userpanel-encounter')};
        }

        if ( Post::formActive() ) {
            $this->_populateFormFields();

            switch ( $this->_action ) {
                case self::KILLS_ADD:
                    FormValidator::validate('kill-add', $this->_formFields);
                    break;
                case self::KILLS_REMOVE:
                    FormValidator::validate('kill-remove', $this->_formFields);
                    break;
                case self::KILLS_EDIT:
                    FormValidator::validate('kill-edit', $this->_formFields);
                    break;
            }

            if ( FormValidator::$isFormInvalid ) {
                $this->_dialogOptions = array('title' => 'Error',
                                              'message' => FormValidator::$message,
                                              'type' => 'danger');
                return;
            }

            switch ( $this->_action ) {
                case self::KILLS_ADD:
                    $this->_addKill();
                    $this->_formStatus = 1;
                    break;
                case self::KILLS_REMOVE:
                    $this->_removeKill();
                    $this->_formStatus = 1;
                    break;
                case self::KILLS_EDIT:
                    $this->_editKill();
                    $this->_formStatus = 1;
                    break;
            }
        }
    }

    /**
     * populate form fields object with form values
     * 
     * @return void
     */
    private function _populateFormFields() {
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
    }

    /**
     * remove kill from encounterkills table in database
     * 
     * @return void
     */
    private function _removeKill() {
        DbObjects::removeKill($this->_formFields);

        $this->_removeScreenshot($this->_formFields->guildId, $this->_formFields->encounter);

        $this->_dialogOptions = array('title' => 'Success',
                                      'message' => 'Your kill has been removed successfully!',
                                      'type' => 'success');
    }

    /**
     * edit kill from encounterkills table in database
     * 
     * @return void
     */
    private function _editKill() {
        DbObjects::editKill($this->_formFields);

        if ( !empty($this->_formFields->screenshot['tmp_name']) ) {
            $imagePath = ABS_FOLD_KILLSHOTS . $this->_formFields->guildId . '-' . $this->_formFields->encounter;

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }

            move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);

            $this->_guildDetails = $this->_getUpdatedGuildDetails($this->_guildDetails->_guildId);
        }

        $this->_encounterDetails = $this->_getUpdatedEncounterDetails($this->_guildDetails->_guildId, $this->_encounterDetails->_encounterId);

        $this->_dialogOptions = array('title' => 'Success',
                                      'message' => 'Your kill has been updated successfully!',
                                      'type' => 'success');
    }

    /**
     * add kill from encounterkills table in database
     * 
     * @return void
     */
    private function _addKill() {
        DbObjects::addKill($this->_formFields);

        if ( !empty($this->_formFields->screenshot['tmp_name']) ) {
            $imagePath = ABS_FOLD_KILLSHOTS . $this->_formFields->guildId . '-' . $this->_formFields->encounter;

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }

            move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);
        }

        $this->_guildDetails = $this->_getUpdatedGuildDetails($this->_guildDetails->_guildId);

        $this->_dialogOptions = array('title' => 'Success',
                                      'message' => 'Your kill has been submitted successfully! Standings and Rankings will be updated accordingly!',
                                      'type' => 'success');
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