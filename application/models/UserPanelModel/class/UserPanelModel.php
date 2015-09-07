<?php
class UserPanelModel extends Model {
    protected $_userDetails;
    protected $_userGuilds;
    protected $_guildDetails;
    protected $_encounterDetails;
    protected $_encounterScreenshot;
    protected $_raidTeams;
    protected $_action;
    protected $_formFields;
    protected $_numOfGuilds = 0;
    protected $_validForm = false;
    protected $_dialogOptions;
    protected $_tableHeader;

    public $subModule;

    const MAX_GUILDS      = 3;
    const MAX_RAID_TEAMS  = 3;

    const SUB_GUILD       = 'guild';
    const SUB_USER        = 'user';
    const SUB_KILLS       = 'kills';

    const USER_EMAIL      = 'email';
    const USER_PASSWORD   = 'password';

    const GUILD_ADD       = 'add';
    const GUILD_EDIT      = 'edit';
    const GUILD_RAID_TEAM = 'raid-team';
    const GUILD_REMOVE    = 'remove';

    const KILLS_ADD       = 'add';
    const KILLS_REMOVE    = 'remove';
    const KILLS_EDIT      = 'edit';

    const GUILD_PROFILE = array(
            'Date Created'    => '_dateCreated',
            'Server'          => '_serverLink',
            'Country'         => '_countryLink',
            'Faction'         => '_faction',
            'Guild Leader(s)' => '_leader',
            'Website'         => '_websiteLink',
            'Social Media'    => '_socialNetworks',
            'World Firsts'    => '_worldFirst',
            'Region Firsts'   => '_regionFirst',
            'Server Firsts'   => '_serverFirst',
            'Status'          => '_active'
        );

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

    const PAGE_TITLE = 'User Panel';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_userDetails = $_SESSION['userDetails'];
        $this->_userDetails = $this->_getUpdatedUserDetails($this->_userDetails->_userId);
        $this->_userGuilds  = $this->_getUserGuilds($this->_userDetails->_userId);

        if ( !empty($params) ) {
            if ( isset($params[0]) ) { $this->subModule         = strtolower($params[0]); }
            if ( isset($params[1]) ) { $this->_action           = strtolower($params[1]); }
            if ( isset($params[2]) ) { $this->_guildDetails = $this->_getCorrectGuild($params[2]); }
            if ( isset($params[3]) ) {
                if ( isset($this->_guildDetails->_encounterDetails->{$params[3]}) ) {
                    $this->_encounterDetails = $this->_guildDetails->_encounterDetails->{$params[3]};
                }
            }
        }

        $this->_tableHeader = self::TABLE_HEADER_PROGRESSION;

        $pathToCP = HOST_NAME . '/userpanel';

        switch($this->subModule) {
            case self::SUB_GUILD:
                $this->_formFields = new GuildFormFields();

                if ( Post::formActive() ) {
                    $this->_processGuildForm();

                    if ( $this->_validForm ) {
                        switch($this->_action) {
                            case self::GUILD_ADD:
                                $this->_addGuild();
                                break;
                            case self::GUILD_REMOVE:
                                $this->_removeGuild();
                                break;
                            case self::GUILD_EDIT:
                                $this->_editGuild();
                                break;
                            case self::GUILD_RAID_TEAM:
                                $this->_addRaidTeam();
                                break;
                        }

                        header('Location: ' . $pathToCP);
                    }
                }

                break;
            case self::SUB_USER:
                $this->_formFields = new UserFormFields();

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

                break;
            case self::SUB_KILLS:
                $this->_formFields = new KillSubmissionFormFields();
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

                        header('Location: ' . $pathToCP);
                    }
                }

                break;
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
     * process submitted guild form
     * 
     * @return void
     */
    private function _processGuildForm() {
        $this->_formFields->guildId     = Post::get('userpanel-guild-id');
        $this->_formFields->guildName   = Post::get('userpanel-guild-name');
        $this->_formFields->faction     = Post::get('userpanel-faction');
        $this->_formFields->server      = Post::get('userpanel-server');
        $this->_formFields->country     = Post::get('userpanel-country');
        $this->_formFields->guildLeader = Post::get('userpanel-guild-leader');
        $this->_formFields->website     = Post::get('userpanel-website');
        $this->_formFields->facebook    = Post::get('userpanel-facebook');
        $this->_formFields->twitter     = Post::get('userpanel-twitter');
        $this->_formFields->google      = Post::get('userpanel-google');
        $this->_formFields->guildLogo   = Post::get('userpanel-guild-logo');

        if ( ($this->_action == self::GUILD_ADD 
             || $this->_action == self::GUILD_EDIT
             || $this->_action == self::GUILD_RAID_TEAM) && !empty($this->_formFields->guildName)
             && !empty($this->_formFields->faction)
             && !empty($this->_formFields->server) 
             && !empty($this->_formFields->country) ) {
                $this->_validForm = true;
        } elseif ( $this->_action == self::GUILD_REMOVE && !empty($this->_formFields->guildId ) ) {
            $this->_validForm = true;
        }
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

    /**
     * get child raid teams of a guild
     * 
     * @param  integer      $guildId      [ id of guild ]
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return array [ array of raid teams ]
     */
    private function _getRaidTeams($guildId, $guildDetails) {
        if ( empty($guildDetails->_child) ) { return array(); }

        $raidTeamArray   = null;
        $raidTeamIdArray = explode('||', $guildDetails->_child);

        foreach( $raidTeamIdArray as $guildId) {
            $raidTeamArray[$guildId] = CommonDataContainer::$guildArray[$guildId];
        }

        return $raidTeamArray;
    }

    /**
     * get the correct guild based on the userId
     * 
     * @param  integer $guildId [ id of guild ]
     * 
     * @return GuildDetails $guildDetails [ guild details object ]
     */
    private function _getCorrectGuild($guildId) {
        $guildDetails = CommonDataContainer::$guildArray[$guildId];

        if ( !empty($guildDetails) && $guildDetails->_creatorId == $this->_userDetails->_userId ) {
            $this->_raidTeams[$guildId] = $this->_getRaidTeams($guildId, $guildDetails);
        }

        if ( isset($guildDetails) ) {
            $guildDetails->generateEncounterDetails('');
        }

        return $guildDetails;
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
     * update guild in database
     * 
     * @return void
     */
    private function _editGuild() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::editGuild($this->_formFields, $this->_guildDetails);
        if ( !empty($this->_formFields->guildLogo['tmp_name']) ) { $this->_assignGuildLogo($this->_guildDetails->_guildId); }
    }

    /**
     * remove guild in database
     * 
     * @return void
     */
    private function _removeGuild() {
        DBObjects::removeGuild($this->_formFields);

        // Guild is a child of a parent guild, update parent's info
        if ( !empty($this->_guildDetails->_parent) ) {
            $parentId    = $this->_guildDetails->_parent;
            $parentGuild = CommonDataContainer::$guildArray[$parentId];

            $parentGuildChildren = $parentGuild->_child;

            $childrenIdArray = explode('||', $parentGuildChildren);

            foreach( $childrenIdArray as $index => $guildId ) {
                if ( $childrenIdArray[$index] == $this->_guildDetails->_guildId ) { unset($childrenIdArray[$index]); } 
            }

            $sqlChild = '';

            if ( !empty($childrenIdArray[$guildId]) ) {
                $sqlChild = implode("||", $childrenIdArray);
            }

            DBObjects::removeChildGuild($sqlChild, $parentId);
        } else if ( !empty($this->_guildDetails->_child) ) {
            $childrenIdArray = explode('||', $this->_guildDetails->_child);

            foreach( $childrenIdArray as $index => $guildId ) {
                 $childForm = new stdClass();
                 $childForm->guildId = $guildId;

                 DBObjects::removeGuild($childForm);
                 $this->_removeGuildLogo($childForm->guildId);
            }
        }

        $this->_removeGuildLogo($this->_formFields->guildId);
    }

    /**
     * add guild into database
     * 
     * @return void
     */
    private function _addGuild() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addGuild($this->_formFields);
        $this->_assignGuildLogo(DBObjects::$insertId);
    }

    /**
     * add guild into database
     * 
     * @return void
     */
    private function _addRaidTeam() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addChildGuild($this->_formFields, $this->_userDetails->_userId, $this->_guildDetails);
        $this->_copyParentGuildLogo($this->_guildDetails->_guildId, DBObjects::$insertId);
    }

    /**
     * copy the parent guild logo onto the child guild logo
     * 
     * @return void
     */
    private function _copyParentGuildLogo($parentId, $childId) {
        $parentPath = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' . $parentId;
        $childPath  = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' . $childId;

        copy($parentPath, $childPath);
    }

    /**
     * assign guild logo to guild after being created, default if no logo is uploaded
     * 
     * @return void
     */
    private function _assignGuildLogo($guildId) {
        $imagePath        = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' . $guildId;
        $defaultImagePath = ABS_FOLD_SITE_LOGOS . 'guild_default_logo.png';

        if ( Functions::validateImage($this->_formFields->guildLogo) ) {
            if ( !file_exists($imagePath) ) {
                move_uploaded_file($this->_formFields->guildLogo['tmp_name'], $imagePath);
            } else {
                copy($this->_formFields->guildLogo['tmp_name'], $imagePath);
            }
        } else {
            if ( !file_exists($imagePath) ) {
                copy($defaultImagePath, $imagePath);
            }
        }
    }

    /**
     * remove guild logo image from filesystem
     * 
     * @return void
     */
    private function _removeGuildLogo($guildId) {
        $imagePath = ABS_FOLD_SITE_LOGOS . 'logo-' . $guildId;

        if ( file_exists($imagePath) ) {
            unlink($imagePath);
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
     * get list of guilds based on user logged in
     * 
     * @param  integer $userId [ id of user ]
     * 
     * @return array [ array of guilds ]
     */
    private function _getUserGuilds($userId) {
        $dbh        = DbFactory::getDbh();
        $guildArray = array();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE creator_id='%s'
                AND type='0'", 
                    DbFactory::TABLE_GUILDS, 
                    $userId));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildArray[$row['guild_id']] = new GuildDetails($row);

            $this->_raidTeams[$row['guild_id']] = $this->_getRaidTeams($row['guild_id'], $guildArray[$row['guild_id']]);
            $this->_numOfGuilds++;
        }

        return $guildArray;
    }

    /**
     * get updated user details after submitting update form successfully
     * 
     * @param  integer $userId [ id of user ]
     * 
     * @return User [ user data object ]
     */
    private function _getUpdatedUserDetails($userId) {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE user_id='%s'",
             DbFactory::TABLE_USERS,
             $userId
             ));
        $query->execute();

        while ( $user = $query->fetch(PDO::FETCH_ASSOC) ) {
            $_SESSION['userDetails'] = new User($user);
            $_SESSION['userId']      = $_SESSION['userDetails']->_userId;
            return $_SESSION['userDetails'];
        }
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
            $optionsString .= $this->generateInternalHyperlink(UserPanelModel::SUB_KILLS, UserPanelModel::KILLS_EDIT . '/' . $this->_guildDetails->_guildId .'/' . $encounterId, 'Edit', true);
            $optionsString .= ' | ';
            $optionsString .= $this->generateInternalHyperlink(UserPanelModel::SUB_KILLS, UserPanelModel::KILLS_REMOVE . '/' . $this->_guildDetails->_guildId . '/' . $encounterId, 'Delete', true);

            $newEncounterDetails->_options = $optionsString;

            $this->_guildDetails->_encounterDetails->$encounterId = $newEncounterDetails;
        }
    }

    /**
     * generate model specific internal hyperlinks
     * 
     * @param  string  $subMod   [ name of sub-model ]
     * @param  string  $function [ page function ]
     * @param  string  $text     [ display text ]
     * @param  boolean $link     [ true if want full html, false if just url ]
     * 
     * @return string [ html string with hyperlink ]
     */
    public function generateInternalHyperLink($subMod, $function, $text, $link = true) {
        $url       = PAGE_USER_PANEL . $subMod . '/' . $function;
        $hyperlink = '';

        if ( $link ) {
            $hyperlink = '<a href="' . $url . '" target"_blank">' . $text . '</a>';
        } else {
            $hyperlink = $url;
        }

        return $hyperlink;
    }

    /**
     * get html of sub-model template file
     * 
     * @param  object $data [ page data ]
     * 
     * @return void
     */
    public function getHTML($data) {
        $htmlFile = $this->subModule . '-' . $this->_action . '.html';
        include ABSOLUTE_PATH . '/application/models/UserPanelModel/html/' . $htmlFile;
    }
}