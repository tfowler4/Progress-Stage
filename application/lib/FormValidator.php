<?php

/*
 * class for providing data validation for application forms
 */
class FormValidator {
    public static $isFormInvalid = false;
    public static $invalidField;
    public static $message = '';

    /**
     * calls for specific form validation function
     *
     * @param  string $formName   [ name of form ]
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    public static function validate($formName, $formFields) {
        switch ($formName) {
            case 'register':
                self::_validateRegisterForm($formFields);
                break;
            case 'contactus':
                self::_validateContactUsForm($formFields);
                break;
            case 'kill-add':
                self::_validateAddKillForm($formFields);
                break;
            case 'kill-edit':
                self::_validateEditKillForm($formFields);
                break;
            case 'kill-remove':
                self::_validateRemoveKillForm($formFields);
                break;
            case 'user-email':
                self::_validateUserEmailForm($formFields);
                break;
            case 'user-password':
                self::_validateUserPasswordForm($formFields);
                break;
            case 'guild-add':
                self::_validateAddGuildForm($formFields);
                break;
            case 'guild-edit':
                self::_validateEditGuildForm($formFields);
                break;
            case 'guild-remove':
                self::_validateRemoveGuildForm($formFields);
                break;
            case 'guild-add-raid-team':
                self::_validateAddRaidTeamForm($formFields);
                break;
            case 'login':
                self::_validateLoginForm($formFields);
                break;
            case 'reset':
                self::_validateResetForm($formFields);
                break;
            case 'reset-confirm':
                self::_validateResetConfirmForm($formFields);
                break;
        }
    }

    /**
     * sets invalid field in the message
     * 
     * @return void
     */
    protected static function _returnInvalidField() {
        if ( empty(self::$message) ) {
            self::_setMessage(self::$invalidField);
        }
    }

    /**
     * add new raid team form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateAddRaidTeamForm($formFields) {
        // required fields
        if ( empty($formFields->guildName) ) { self::$isFormInvalid = true; self::$invalidField = 'Guild Name'; self::_returnInvalidField(); }
        if ( empty($formFields->faction) ) {   self::$isFormInvalid = true; self::$invalidField = 'Faction'; self::_returnInvalidField(); }
        if ( empty($formFields->server) ) {    self::$isFormInvalid = true; self::$invalidField = 'Server'; self::_returnInvalidField(); }
        if ( empty($formFields->country) ) {   self::$isFormInvalid = true; self::$invalidField = 'Country'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate guild name/server
        $isGuildInvalid = self::_validateGuildExists($formFields->guildName, $formFields->server);

        if ( $isGuildInvalid ) {
            self::$isFormInvalid = true;
            return;
        }
    }

    /**
     * remove guild form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateRemoveGuildForm($formFields) {
        // required fields
        if ( empty($formFields->guildId) ) { self::$isFormInvalid = true; self::$invalidField = 'Guild'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate guild
        $isGuildInvalid = self::_validateGuild($formFields->guildId);

        if ( $isGuildInvalid ) {
            self::$isFormInvalid = true;
            return;
        }
    }

    /**
     * edit guild details form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateEditGuildForm($formFields) {
        // required fields
        if ( empty($formFields->guildName) ) { self::$isFormInvalid = true; self::$invalidField = 'Guild Name'; self::_returnInvalidField(); }
        if ( empty($formFields->faction) ) {   self::$isFormInvalid = true; self::$invalidField = 'Faction'; self::_returnInvalidField(); }
        if ( empty($formFields->server) ) {    self::$isFormInvalid = true; self::$invalidField = 'Server'; self::_returnInvalidField(); }
        if ( empty($formFields->country) ) {   self::$isFormInvalid = true; self::$invalidField = 'Country'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate guild name/server
        $guildDetails = CommonDataContainer::$guildArray[$formFields->guildId];
        if ( $guildDetails->_server != $formFields->server ) {
            $isGuildInvalid = self::_validateGuildExists($formFields->guildName, $formFields->server);

            if ( $isGuildInvalid ) {
                self::$isFormInvalid = true;
                return;
            }
        }

        // validate website url
        if ( !empty($formFields->website) ) {
            $isWebsiteInvalid = self::_validateWebsiteLink($formFields->website);

            if ( $isWebsiteInvalid ) {
                self::$isFormInvalid = true;
                return;
            }
        }

        // validate logo
        if ( !empty($formFields->guildLogo['tmp_name']) ) {
            if ( !Functions::validateImage($formFields->guildLogo) ) {
                self::$isFormInvalid = true;
                self::_setMessage('Guild Logo', 'There was an error with the file you submitted.');
                return;
            }
        }
    }

    /**
     * add new guild form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateAddGuildForm($formFields) {
        // required fields
        if ( empty($formFields->guildName) ) { self::$isFormInvalid = true; self::$invalidField = 'Guild Name'; self::_returnInvalidField(); }
        if ( empty($formFields->faction) ) {   self::$isFormInvalid = true; self::$invalidField = 'Faction'; self::_returnInvalidField(); }
        if ( empty($formFields->server) ) {    self::$isFormInvalid = true; self::$invalidField = 'Server'; self::_returnInvalidField(); }
        if ( empty($formFields->country) ) {   self::$isFormInvalid = true; self::$invalidField = 'Country'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate guild name/server
        $isGuildInvalid = self::_validateGuildExists($formFields->guildName, $formFields->server);

        if ( $isGuildInvalid ) {
            self::$isFormInvalid = true;
            return;
        }

        // validate website url
        if ( !empty($formFields->website) ) {
            $isWebsiteInvalid = self::_validateWebsiteLink($formFields->website);

            if ( $isWebsiteInvalid ) {
                self::$isFormInvalid = true;
                return;
            }
        }

        // validate logo
        if ( !empty($formFields->guildLogo['tmp_name']) ) {
            if ( !Functions::validateImage($formFields->guildLogo) ) {
                self::$isFormInvalid = true;
                self::_setMessage('Guild Logo', 'There was an error with the file you submitted.');
                return;
            }
        }
    }

    /**
     * update user password form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateUserPasswordForm($formFields) {
        // required fields
        if ( empty($formFields->userId) ) {            self::$isFormInvalid = true; self::$invalidField = 'User'; self::_returnInvalidField(); }
        if ( empty($formFields->oldPassword) ) {       self::$isFormInvalid = true; self::$invalidField = 'Current Password'; self::_returnInvalidField(); }
        if ( empty($formFields->newPassword) ) {       self::$isFormInvalid = true; self::$invalidField = 'New Password'; self::_returnInvalidField(); }
        if ( empty($formFields->retypeNewPassword) ) { self::$isFormInvalid = true; self::$invalidField = 'Retype New Password'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate old password
        if ( self::encryptPasscode($formFields->oldPassword) != $_SESSION['userDetails']->_encrypedPassword ) {
            self::$isFormInvalid = true;
            self::_setMessage('Password', 'Current password is incorrect. Please try again.');
            self::$invalidField = 'Password';
            return;
        }

        // validate new password
        $isPasswordInvalid = self::_validatePassword($formFields->newPassword, $formFields->retypeNewPassword);

        if ( $isPasswordInvalid ) {
            self::$isFormInvalid = true;
            self::$invalidField = 'Password';
            return;
        }
    }

    /**
     * update user email form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateUserEmailForm($formFields) {
        // required fields
        if ( empty($formFields->userId) ) { self::$isFormInvalid = true; self::$invalidField = 'User'; self::_returnInvalidField(); }
        if ( empty($formFields->email) ) {  self::$isFormInvalid = true; self::$invalidField = 'Email'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate email
        $isEmailInvalid = self::_validateEmail($formFields->email);

        if ( $isEmailInvalid ) {
            self::$isFormInvalid = true;
            self::$invalidField = 'Email';
            return;
        }
    }

    /**
     * remove guild kill form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateRemoveKillForm($formFields) {
        // required fields
        if ( empty($formFields->guildId) ) {    self::$isFormInvalid = true; self::$invalidField = 'Guild'; self::_returnInvalidField(); }
        if ( empty($formFields->encounter) ) {  self::$isFormInvalid = true; self::$invalidField = 'Encounter'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate guild
        $isGuildInvalid = self::_validateGuild($formFields->guildId);

        if ( $isGuildInvalid ) {
            self::$isFormInvalid = true;
            return;
        }

        // check if encounter has any encounters dependant
        $dbh              = DbFactory::getDbh();
        $encounterDetails = CommonDataContainer::$encounterArray[$formFields->encounter];

        $query = $dbh->prepare(sprintf(
            "SELECT encounter_id,
                    name,
                    dungeon,
                    dungeon_id,
                    players,
                    tier,
                    mob_type,
                    encounter_name,
                    encounter_short_name,
                    date_launch,
                    mob_order,
                    req_encounter
               FROM %s
              WHERE req_encounter=%d", 
                    DbFactory::TABLE_ENCOUNTERS,
                    $formFields->encounter
                ));
        $query->execute();

        if ( $query->rowCount() > 0 ) {
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $dependentEncounterId      = $row['encounter_id'];
                $dependentEncounterDetails = CommonDataContainer::$encounterArray[$dependentEncounterId];

                $query = $dbh->prepare(sprintf(
                    "SELECT kill_id,
                            guild_id,
                            encounter_id,
                            dungeon_id,
                            tier,
                            raid_size,
                            datetime,
                            date,
                            time,
                            time_zone,
                            server,
                            videos,
                            server_rank,
                            region_rank,
                            world_rank,
                            country_rank
                       FROM %s
                      WHERE guild_id=%d
                        AND encounter_id=%d", 
                            DbFactory::TABLE_KILLS, 
                            $formFields->guildId,
                            $dependentEncounterId
                        ));
                $query->execute();

                if ( $query->rowCount() > 0 ) {
                    self::_setMessage('Encounter', $dependentEncounterDetails->_name .' encounter is dependent on the encounter you are attempting to remove. Please remove the ' . $encounterDetails->_name . ' encounter first.');
                    self::$isFormInvalid = true;
                    return;
                }
            }
        }
    }

    /**
     * edit guild kill form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateEditKillForm($formFields) {
        // required fields
        if ( empty($formFields->guildId) ) {    self::$isFormInvalid = true; self::$invalidField = 'Guild'; self::_returnInvalidField(); }
        if ( empty($formFields->encounter) ) {  self::$isFormInvalid = true; self::$invalidField = 'Encounter'; self::_returnInvalidField(); }
        if ( empty($formFields->dateMonth) ) {  self::$isFormInvalid = true; self::$invalidField = 'Month'; self::_returnInvalidField(); }
        if ( empty($formFields->dateDay) ) {    self::$isFormInvalid = true; self::$invalidField = 'Day'; self::_returnInvalidField(); }
        if ( empty($formFields->dateYear) ) {   self::$isFormInvalid = true; self::$invalidField = 'Year'; self::_returnInvalidField(); }
        if ( empty($formFields->dateHour) ) {   self::$isFormInvalid = true; self::$invalidField = 'Hour'; self::_returnInvalidField(); }
        if ( empty($formFields->dateMinute) ) { self::$isFormInvalid = true; self::$invalidField = 'Minute'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate guild
        $isGuildInvalid = self::_validateGuild($formFields->guildId);

        if ( $isGuildInvalid ) {
            self::$isFormInvalid = true;
            return;
        }

        // validate screenshot
        if ( !empty($image['tmp_name']) ) {
            $isScreenshotInvalid = self::_validateScreenshot($formFields->screenshot);
            if ( $isScreenshotInvalid ) {
                self::$isFormInvalid = true;
                return;
            }
        }
    }

    /**
     * add new guild kill form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateAddKillForm($formFields) {
        // required fields
        if ( empty($formFields->guildId) ) {    self::$isFormInvalid = true; self::$invalidField = 'Guild'; self::_returnInvalidField(); }
        if ( empty($formFields->encounter) ) {  self::$isFormInvalid = true; self::$invalidField = 'Encounter'; self::_returnInvalidField(); }
        if ( empty($formFields->dateMonth) ) {  self::$isFormInvalid = true; self::$invalidField = 'Month'; self::_returnInvalidField(); }
        if ( empty($formFields->dateDay) ) {    self::$isFormInvalid = true; self::$invalidField = 'Day'; self::_returnInvalidField(); }
        if ( empty($formFields->dateYear) ) {   self::$isFormInvalid = true; self::$invalidField = 'Year'; self::_returnInvalidField(); }
        if ( empty($formFields->dateHour) ) {   self::$isFormInvalid = true; self::$invalidField = 'Hour'; self::_returnInvalidField(); }
        if ( empty($formFields->dateMinute) ) { self::$isFormInvalid = true; self::$invalidField = 'Minute'; self::_returnInvalidField(); }
        if ( empty($formFields->screenshot) ) { self::$isFormInvalid = true; self::$invalidField = 'Screenshot'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) { return; }

        // validate guild
        $isGuildInvalid = self::_validateGuild($formFields->guildId);

        if ( $isGuildInvalid ) {
            self::$isFormInvalid = true;
            return;
        }

        // validate encounter
        $isEncounterInvalid = self::_validateEncounter($formFields->guildId, $formFields->encounter);

        if ( $isEncounterInvalid ) {
            self::$isFormInvalid = true;
            return;
        }

        // validate if encounter has special requirements

        // validate screenshot
        $isScreenshotInvalid = self::_validateScreenshot($formFields->screenshot);
        if ( $isScreenshotInvalid ) {
            self::$isFormInvalid = true;
            return;
        }
    }

    /**
     * reset password confirmation form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateResetConfirmForm($formFields) {
        // required fields
        if ( empty($formFields->newPassword) ) {       self::$isFormInvalid = true; self::$invalidField = 'New Password'; self::_returnInvalidField(); }
        if ( empty($formFields->retypeNewPassword) ) { self::$isFormInvalid = true; self::$invalidField = 'Retype New Password'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) {
            self::_setMessage(self::$invalidField);
            return;
        }

        // validate new password
        $isPasswordInvalid = self::_validatePassword($formFields->newPassword, $formFields->retypeNewPassword);

        if ( $isPasswordInvalid ) {
            self::$isFormInvalid = true;
            self::$invalidField = 'Password';
            return;
        }
    }

    /**
     * reset password form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateResetForm($formFields) {
        // required fields
        if ( empty($formFields->email) ) { self::$isFormInvalid = true; self::$invalidField = 'Email'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) {
            self::_setMessage(self::$invalidField);
            return;
        }

        // validate email
        $isEmailInvalid = self::_validateEmail($formFields->email);

        if ( $isEmailInvalid ) {
            self::$isFormInvalid = true;
            self::$invalidField = 'Email';
            return;
        }
    }

    /**
     * login form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateLoginForm($formFields) {
        // required fields
        if ( empty($formFields->username) ) { self::$isFormInvalid = true; self::$invalidField = 'Username/Email'; self::_returnInvalidField(); }
        if ( empty($formFields->password) ) { self::$isFormInvalid = true; self::$invalidField = 'Password'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) {
            self::_setMessage(self::$invalidField);
            return;
        }
    }

    /**
     * register form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateRegisterForm($formFields) {
        // required fields
        if ( empty($formFields->username) ) {       self::$isFormInvalid = true; self::$invalidField = 'Username'; self::_returnInvalidField(); }
        if ( empty($formFields->email) ) {          self::$isFormInvalid = true; self::$invalidField = 'Email Address'; self::_returnInvalidField(); }
        if ( empty($formFields->password) ) {       self::$isFormInvalid = true; self::$invalidField = 'Password'; self::_returnInvalidField(); }
        if ( empty($formFields->retypePassword) ) { self::$isFormInvalid = true; self::$invalidField = 'Retype Password'; self::_returnInvalidField(); }
        if ( empty($formFields->guildName) ) {      self::$isFormInvalid = true; self::$invalidField = 'Guild Name'; self::_returnInvalidField(); }
        if ( empty($formFields->faction) ) {        self::$isFormInvalid = true; self::$invalidField = 'Faction'; self::_returnInvalidField(); }
        if ( empty($formFields->server) ) {         self::$isFormInvalid = true; self::$invalidField = 'Server'; self::_returnInvalidField(); }
        if ( empty($formFields->country) ) {        self::$isFormInvalid = true; self::$invalidField = 'Country'; self::_returnInvalidField(); }
        if ( empty($formFields->isImportant) ) {    self::$isFormInvalid = true; self::$invalidField = 'Terms of Service'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) {
            self::_setMessage(self::$invalidField);
            return;
        }

        // validate email
        $isEmailInvalid = self::_validateEmail($formFields->email);

        if ( $isEmailInvalid ) {
            self::$isFormInvalid = true;
            self::$invalidField = 'Email';
            return;
        }

        // validate password
        $isPasswordInvalid = self::_validatePassword($formFields->password, $formFields->retypePassword);

        if ( $isPasswordInvalid ) {
            self::$isFormInvalid = true;
            self::$invalidField = 'Password';
            return;
        }
    }

    /**
     * contact us form validation
     * 
     * @param  object $formFields [ form fields object ]
     * 
     * @return void
     */
    protected static function _validateContactUsForm($formFields) {
        // required fields
        if ( empty($formFields->email) ) {    self::$isFormInvalid = true; self::$invalidField = 'Email'; self::_returnInvalidField(); }
        if ( empty($formFields->message) ) {  self::$isFormInvalid = true; self::$invalidField = 'Message'; self::_returnInvalidField(); }
        if ( empty($formFields->feedback) ) { self::$isFormInvalid = true; self::$invalidField = 'Feedback Type'; self::_returnInvalidField(); }

        // if form is invalid after empty fields check, post message of that field
        if ( self::$isFormInvalid ) {
            self::_setMessage(self::$invalidField);
            return;
        }

        // validate email
        $isEmailInvalid = self::_validateEmail($formFields->email);

        if ( $isEmailInvalid ) {
            self::$isFormInvalid = true;
            self::$invalidField = 'Email';
            return;
        }
    }

    /**
     * validate screenshot submitted from form to ensure format and size are valid
     * 
     * @param  array [ image from form as array ]
     * 
     * @return boolean [ true if image is invalid ]
     */
    protected static function _validateScreenshot($image) {
        // checks if an image was submitted
        if ( empty($image['tmp_name']) ) {
            self::_setMessage('Screenshot', 'Please submit a screenshot');
            return true;
        }

        $validExtensions = unserialize(VALID_IMAGE_FORMATS);
        $imageFileName   = $image['name'];
        $imageFileTemp   = $image['tmp_name'];
        $imageFileType   = exif_imagetype($imageFileTemp);
        $imageFileSize   = $image['size'];
        $imageFileErr    = $image['error'];

        // checks if Image is a valid format
        $isImageFormatValid = false;

        $numOfExtensions = count($validExtensions);
        for ( $i = 0; $i < $numOfExtensions; $i++ ) {
            if ( $imageFileType == $validExtensions[$i] ) {

                $isImageFormatValid = true;
                break;
            }
        }

        if ( $isImageFormatValid == false ) {
            self::_setMessage('Screenshot', 'Please use an acceptable file format of JPEG/PNG/GIF/BMP');
        }

        // checks if Image is of correct size
        $isImageFileSizeValid = true;

        if ( !getimagesize($imageFileTemp) || !($imageFileSize < MAX_IMAGE_SIZE) ) {
            $isImageFileSizeValid = false;
            self::_setMessage('Screenshot', 'Screenshot file size exceeds the maximum of ' . MAX_IMAGE_SIZE);
        }

        // checks if Image has any errors
        $isImageFileErrorFree = true;

        if ( !empty($imageFileErr) ) {
            $isImageFileErrorFree = false;
            self::_setMessage('Screenshot', 'Image file contains some unknown errors. Please try submitting another image.');
        }

        // validate all checks clear
        if ( $isImageFormatValid == false || $isImageFileSizeValid == false || $isImageFileErrorFree == false ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * validate encounter if it already exists
     * 
     * @return boolean [ true if submission is valid ]
     */
    protected static function _validateEncounter($guildId, $encounterId) {
        $isEncounterInvalid = false;
        $encounterDetails   = CommonDataContainer::$encounterArray[$encounterId];

        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT kill_id,
                    guild_id,
                    encounter_id,
                    dungeon_id,
                    tier,
                    raid_size,
                    datetime,
                    date,
                    time,
                    time_zone,
                    server,
                    videos,
                    server_rank,
                    region_rank,
                    world_rank,
                    country_rank
               FROM %s
              WHERE guild_id=%d
                AND encounter_id=%d", 
                    DbFactory::TABLE_KILLS, 
                    $guildId,
                    $encounterId
                ));
        $query->execute();

        if ( $query->rowCount() > 0 ) {
            $isEncounterInvalid = true;
            self::_setMessage('Encounter', $encounterDetails->_name .' encounter has already been submitted for this guild, please select another encounter.');
        }

        // check if encounter has any required encounters
        if ( !empty($encounterDetails->_reqEncounter) || $encounterDetails->_reqEncounter > 0 ) {
            $reqEncounterId      = $encounterDetails->_reqEncounter;
            $reqEncounterDetails = CommonDataContainer::$encounterArray[$reqEncounterId];

            $query = $dbh->prepare(sprintf(
                "SELECT kill_id,
                        guild_id,
                        encounter_id,
                        dungeon_id,
                        tier,
                        raid_size,
                        datetime,
                        date,
                        time,
                        time_zone,
                        server,
                        videos,
                        server_rank,
                        region_rank,
                        world_rank,
                        country_rank
                   FROM %s
                  WHERE guild_id=%d
                    AND encounter_id=%d", 
                        DbFactory::TABLE_KILLS, 
                        $guildId,
                        $reqEncounterId
                    ));
            $query->execute();

            if ( $query->rowCount() == 0 ) {
                $isEncounterInvalid = true;
                self::_setMessage('Encounter', $reqEncounterDetails->_name .' is required before submitting this encounter.');
            }
        }

        return $isEncounterInvalid;
    }

    /**
     * website url validation
     * 
     * @param  string $url [ website url ]
     * 
     * @return boolean [ true if website url does not validate ]
     */
    protected static function _validateWebsiteLink($url) {
        $isWebsiteInvalid = false;

        // remove illegal characters
        $url = filter_var($url, FILTER_SANITIZE_URL);

        if ( filter_var($url, FILTER_VALIDATE_URL) === false ) {
            $isWebsiteInvalid = true;
            self::_setMessage('Website', 'Please enter a valid website url.');
        }

        return $isWebsiteInvalid;
    }

    /**
     * guild details validation
     * 
     * @param  integer $guildId [ id of guild ]
     * 
     * @return boolean [ true if guild does not exist ]
     */
    protected static function _validateGuild($guildId) {
        $isGuildInvalid = false;

        if ( !isset(CommonDataContainer::$guildArray[$guildId]) ) {
            $isGuildInvalid = true;
            self::_setMessage('Guild', 'Please select a valid guild.');
        }

        return $isGuildInvalid;
    }

    /**
     * guild creation validation
     * 
     * @param  string $name [ name of guild ]
     * @param  string $server [ guild server ]
     * 
     * @return boolean [ true if guild already exists on server ]
     */
    protected static function _validateGuildExists($name, $server) {
        $isGuildInvalid = false;
        $dbh            = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT guild_id, name, server
               FROM %s
              WHERE name='%s'
                AND server='%s'", 
                    DbFactory::TABLE_GUILDS, 
                    $name,
                    $server
                ));
        $query->execute();

        if ( $query->rowCount() > 0 ) {
            $isGuildInvalid = true;
            self::_setMessage('Guild', $name .' on ' . $server . ' already exists.');
        }

        return $isGuildInvalid;
    }

    /**
     * email address validation
     * 
     * @param  string $email [ email address ]
     * 
     * @return boolean [ true if passwords do not validate ]
     */
    protected static function _validateEmail($email) {
        $isEmailInvalid = false;

        // remove illegal characters
        $email = filter_var($email, FILTER_SANITIZE_EMAIL);

        if ( filter_var($email, FILTER_VALIDATE_EMAIL) === false ) {
            $isEmailInvalid = true;
            self::_setMessage('Email', 'Please use a valid email address.');
        }

        return $isEmailInvalid;
    }

    /**
     * password validation
     * 
     * @param  string $password       [ password entry one ]
     * @param  string $reTypePassword [ password entry two ]
     * 
     * @return boolean [ true if passwords do not validate ]
     */
    protected static function _validatePassword($password, $reTypePassword) {
        // check if passwords match
        $doPasswordsMatch = true;

        if ( $password !== $reTypePassword ) {
            $doPasswordsMatch = false;
            self::_setMessage('Password', 'Password fields must match.');
        }

        // check if password is of correct length
        $isPasswordLengthValid = true;

        if ( strlen($password) < PASSWORD_MINIMUM ) {
            $isPasswordLengthValid = false;
            self::_setMessage('Password', 'Password must be a minimum of ' . PASSWORD_MINIMUM .' characters.');
        }

        // validate all checks clear
        if ( $doPasswordsMatch == false || $isPasswordLengthValid == false ) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * encrypt the submitted password
     * 
     * @param  string $password [ unencrypted password ]
     * 
     * @return string [ encrypted password ]
     */
    public static function encryptPasscode($password) {
        $encryptedPasscode = sha1($password);

        for ( $i = 0; $i < 5; $i++ ) {
            $encryptedPasscode = sha1($encryptedPasscode.$i);
        }

        crypt($encryptedPasscode, '');

        return $encryptedPasscode;
    }

    /**
     * set dialog message 
     * 
     * @param string $field   [ form field name ]
     * @param string $message [ dialog message ]
     */
    protected static function _setMessage($field, $message = '') {
        if ( empty(self::$message) ) {
            if ( empty($message) ) {
                self::$message = sprintf('Please ensure the "%s" field is completed.', $field);
            } else {
                self::$message = $message;
            }
        }
    }
}