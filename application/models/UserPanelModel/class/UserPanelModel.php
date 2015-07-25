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

    public function __construct($module, $params) {
        parent::__construct($module);

        $this->title = self::PAGE_TITLE;

        $this->_userDetails = $_SESSION['userDetails'];
        $this->_userDetails = $this->getUpdatedUserDetails($this->_userDetails->_userId);
        $this->_userGuilds  = $this->getUserGuilds($this->_userDetails->_userId);

        if ( !empty($params) ) {
            if ( isset($params[0]) ) { $this->subModule         = strtolower($params[0]); }
            if ( isset($params[1]) ) { $this->_action           = strtolower($params[1]); }
            if ( isset($params[2]) ) { $this->_guildDetails = $this->getCorrectGuild($params[2]); }
            if ( isset($params[3]) ) {
                if ( isset($this->_guildDetails->_encounterDetails->{$params[3]}) ) {
                    $this->_encounterDetails = $this->_guildDetails->_encounterDetails->{$params[3]};
                }
            }
        }

        $this->_tableHeader = self::TABLE_HEADER_PROGRESSION;

        $this->loadFormFields();

        $pathToCP = HOST_NAME . '/userpanel';

        switch($this->subModule) {
            case self::SUB_GUILD:
                $this->_formFields = new GuildFormFields();

                if ( Post::formActive() ) {
                    $this->processGuildForm();

                    if ( $this->_validForm ) {
                        switch($this->_action) {
                            case self::GUILD_ADD:
                                $this->addGuild();
                                break;
                            case self::GUILD_REMOVE:
                                $this->removeGuild();
                                break;
                            case self::GUILD_EDIT:
                                $this->editGuild();
                                break;
                            case self::GUILD_RAID_TEAM:
                                $this->addRaidTeam();
                                break;
                        }

                        header('Location: ' . $pathToCP);
                    }
                }

                break;
            case self::SUB_USER:
                $this->_formFields = new UserFormFields();

                if ( Post::formActive() ) {
                    $this->processUserForm();

                    if ( $this->_validForm ) {
                        switch($this->_action) {
                            case self::USER_EMAIL:
                                $this->updateEmail();
                                break;
                            case self::USER_PASSWORD:
                                if ( $this->validatePassword() ) {
                                    $this->updatePassword();
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
                    $this->processKillForm();

                    if ( $this->_validForm ) {
                        switch($this->_action) {
                            case self::KILLS_ADD:
                                $this->addKill();
                                break;
                            case self::KILLS_REMOVE:
                                $this->removeKill();
                                break;
                            case self::KILLS_EDIT:
                                $this->editKill();
                                break;
                        }

                        header('Location: ' . $pathToCP);
                    }
                }

                break;
        }
    }

    public function processUserForm() {
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

    public function processKillForm() {
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

    public function processGuildForm() {
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

    public function validatePassword() {
        if ( $this->encryptPasscode($this->_userDetails->userName, $this->_formFields->oldPassword) == $this->_userDetails->_encrypedPassword ) {
            if ( ($this->_formFields->newPassword == $this->_formFields->retypeNewPassword)
                 && strlen($this->_formFields->newPassword) >= PASSWORD_MINIMUM) {
                return true;
            }
        }

        return false;
    }

    public function getRaidTeams($guildId, $guildDetails) {
        if ( empty($guildDetails->_child) ) { return array(); }
        $raidTeamArray   = null;
        $raidTeamIdArray = explode('||', $guildDetails->_child);

        foreach( $raidTeamIdArray as $guildId) {
            $raidTeamArray[$guildId] = CommonDataContainer::$guildArray[$guildId];
        }

        return $raidTeamArray;
    }

    public function getCorrectGuild($guildId) {
        $guildDetails = CommonDataContainer::$guildArray[$guildId];

        if ( !empty($guildDetails) && $guildDetails->_creatorId == $this->_userDetails->_userId ) {
            $this->_raidTeams[$guildId] = $this->getRaidTeams($guildId, $guildDetails);
        }

        if ( isset($guildDetails) ) {
            $guildDetails->generateEncounterDetails('');
        }

        return $guildDetails;
    }

    public function updateEmail() {
        DBObjects::editUserEmail($this->_formFields);
    }

    public function updatePassword() {
        $this->_formFields->newPassword = $this->encryptPasscode($this->_userDetails->userName, $this->_formFields->newPassword);

        DBObjects::editUserPassword($this->_formFields);
    }

    public function editGuild() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::editGuild($this->_formFields, $this->_guildDetails);
        if ( !empty($this->_formFields->guildLogo['tmp_name']) ) { $this->assignGuildLogo($this->_guildDetails->_guildId); }
    }

    public function removeGuild() {
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
                 $this->removeGuildLogo($childForm->guildId);
            }
        }

        $this->removeGuildLogo($this->_formFields->guildId);
    }

    public function addGuild() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addGuild($this->_formFields);
        $this->assignGuildLogo(DBObjects::$insertId);
    }

    public function addRaidTeam() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addChildGuild($this->_formFields, $this->_userDetails->_userId, $this->_guildDetails);
        $this->copyParentGuildLogo($this->_guildDetails->_guildId, DBObjects::$insertId);
    }

    public function copyParentGuildLogo($parentId, $childId) {
        $parentPath = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/guilds/logos/logo-' . $parentId);
        $childPath  = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/guilds/logos/logo-' . $childId);

        copy($parentPath, $childPath);
    }

    public function assignGuildLogo($guildId) {
        $imagePath        = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/guilds/logos/logo-' . $guildId);
        $defaultImagePath = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/logos/site/guild_default_logo.png');

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

    public function removeGuildLogo($guildId) {
        $imagePath = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/guilds/logos/logo-' . $guildId);

        if ( file_exists($imagePath) ) {
            unlink($imagePath);
        }
    }

    public function removeScreenshot($guildId, $encounterId) {
        $imagePath = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/screenshots/killshots/' . $guildId . '-' . $encounterId);

        if ( file_exists($imagePath) ) {
            unlink($imagePath);
        }
    }

    public function removeKill() {
        $progressionString = $this->removeKillFromProgressionString($this->_guildDetails->_progression);

        DBObjects::removeKill($this->_formFields, $progressionString);
        $this->removeScreenshot($this->_formFields->guildId, $this->_formFields->encounter);
    }

    public function editKill() {
        $progressionString = $this->removeKillFromProgressionString($this->_guildDetails->_progression);
        $progressionString = $this->generateProgressionString($progressionString);

        DBObjects::editKill($this->_formFields, $progressionString);

        if ( Functions::validateImage($this->_formFields->screenshot) ) {
            $imagePath = strtolower(ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/screenshots/killshots/' . $this->_formFields->guildId . '-' . $this->_formFields->encounter);

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }

            move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);
        }
    }

    public function addKill() {
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

    public function removeKillFromProgressionString($progressionString) {
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

    public function generateProgressionString($progressionString) {
        echo $progressionString."<br><br>";
        $insertString = Functions::generateDBInsertString($progressionString, $this->_formFields, $this->_guildDetails->_guildId);

        return $insertString;
    }

    public function getUserGuilds($userId) {
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

            $this->_raidTeams[$row['guild_id']] = $this->getRaidTeams($row['guild_id'], $guildArray[$row['guild_id']]);
            $this->_numOfGuilds++;
        }

        return $guildArray;
    }

    public function getUpdatedUserDetails($userId) {
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

    public function encryptPasscode($username, $password) {
        $encryptedPasscode = sha1($password);

        for ( $i = 0; $i < 5; $i++ ) {
            $encryptedPasscode = sha1($encryptedPasscode.$i);
        }

        crypt($encryptedPasscode, '');

        return $encryptedPasscode;
    }

    public function mergeOptionsToEncounters() {
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

    public function loadFormFields() {
        require 'UserPanelFormFields.php';
    }

    public function getHTML($data) {
        $htmlFile = $this->subModule . '-' . $this->_action . '.html';
        include ABSOLUTE_PATH . '/application/models/UserPanelModel/html/' . $htmlFile;
    }

    public function __destruct() {

    }
}