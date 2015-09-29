<?php

/*
 * class for providing data validation for application forms
 */
class FormValidator {
    public static $isFormInvalid = false;
    public static $invalidField;
    public static $message = '';

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
        }
    }

    protected static function _returnInvalidField() {
        if ( empty(self::$message) ) {
            self::_setMessage(self::$invalidField);
        }
    }

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
            $isWebsiteInvalid = self::_validateWebsiteLink($this->website);

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
            self::_setMessage('Password', 'Password fields must match.');
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
    }

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

        // validate screenshot
        $isScreenshotInvalid = self::_validateScreenshot($formFields->screenshot);
        if ( $isScreenshotInvalid ) {
            self::$isFormInvalid = true;
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

        $count = $query->rowCount();

        if ( $query->rowCount() > 0 ) {
            $isEncounterInvalid = true;
            self::_setMessage('Encounter', $encounterDetails->_name .' encounter has already been submitted for this guild, please select another encounter.');
        }

        return $isEncounterInvalid;
    }

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

    protected static function _validateGuild($guildId) {
        $isGuildInvalid = false;

        if ( !isset(CommonDataContainer::$guildArray[$guildId]) ) {
            $isGuildInvalid = true;
            self::_setMessage('Guild', 'Please select a valid guild.');
        }

        return $isGuildInvalid;
    }

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

        $count = $query->rowCount();

        if ( $query->rowCount() > 0 ) {
            $isGuildInvalid = true;
            self::_setMessage('Guild', $name .' on ' . $server . ' already exists.');
        }

        return $isGuildInvalid;
    }

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