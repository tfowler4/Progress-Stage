<?php

/**
 * register to the website page
 */
class RegisterModel extends Model {
    protected $_newestGuilds;
    protected $_formFields;
    protected $_dialogOptions;

    const PASSWORD_MINIMUM = 3;
    const PAGE_TITLE       = 'Registration';

    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_formFields = new RegisterFormFields();

        $this->_newestGuilds = $this->_getNewestGuilds();

        // submit form if one is active
        if ( Post::formActive() ) {
            $this->_populateFormFields();

            FormValidator::validate('register', $this->_formFields);

            if ( FormValidator::$isFormInvalid ) {
                $this->_dialogOptions = array('title' => 'Error', 'message' => FormValidator::$message);
                return;
            }

            $this->_processRegistration();
        }
    }

    /**
     * populate form fields object with form values
     * 
     * @return void
     */
    private function _populateFormFields() {
        $this->_formFields->username       = Post::get('register-username');
        $this->_formFields->email          = Post::get('register-email');
        $this->_formFields->password       = Post::get('register-password');
        $this->_formFields->retypePassword = Post::get('register-retype-password');
        $this->_formFields->guildName      = Post::get('register-guild-name');
        $this->_formFields->faction        = Post::get('register-faction');
        $this->_formFields->server         = Post::get('register-server');
        $this->_formFields->country        = Post::get('register-country');
        $this->_formFields->guildLeader    = Post::get('register-guild-leader');
        $this->_formFields->website        = Post::get('register-website');
        $this->_formFields->facebook       = Post::get('register-facebook');
        $this->_formFields->twitter        = Post::get('register-twitter');
        $this->_formFields->google         = Post::get('register-google');
        $this->_formFields->guildLogo      = Post::get('register-guild-logo');
        $this->_formFields->isImportant    = Post::get('register-required-tos');
    }

    /**
     * process submitted register form depending on register type
     * 
     * @return void
     */
    private function _processRegistration() {
        $this->_registerUser();
        $this->_registerGuild();
    }

    /**
     * get the newest guilds registered
     * 
     * @return array [ array of guild ]
     */
    private function _getNewestGuilds() {
        $dbh        = DbFactory::getDbh();
        $dataArray  = array();

        $query = $dbh->prepare(sprintf(
            "SELECT guild_id,
                    name,
                    date_created,
                    leader,
                    website,
                    guild_type,
                    schedule,
                    facebook,
                    twitter,
                    google,
                    faction,
                    region,
                    country,
                    server,
                    active,
                    type,
                    creator_id,
                    parent,
                    child,
                    rank_tier,
                    rank_size,
                    rank_dungeon,
                    rank_encounter,
                    rank_tier_size,
                    rank_overall
               FROM %s
           ORDER BY date_created DESC
              LIMIT 10", 
            DbFactory::TABLE_GUILDS
            ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $dataArray[$row['guild_id']] = new GuildDetails($row);
        }

        return $dataArray;
    }

    /**
     * register user to website and redirect to user panel page
     * 
     * @return void
     */
    private function _registerUser() {
        $this->_formFields->password = FormValidator::encryptPasscode($this->_formFields->password);

        DBObjects::addUser($this->_formFields);

        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT user_id,
                    username,
                    email,
                    passcode,
                    active,
                    date_joined,
                    confirmcode,
                    admin
               FROM %s
              WHERE user_id='%s'",
             DbFactory::TABLE_USERS,
             DBObjects::$insertId
             ));
        $query->execute();

        while ( $user = $query->fetch(PDO::FETCH_ASSOC) ) {
            $_SESSION['userId']      = DBObjects::$insertId;
            $_SESSION['logged']      = 'yes';
            $_SESSION['userDetails'] = new User($user);

            $pathToUP = HOST_NAME . '/userpanel';
            header('Location: ' . $pathToUP);
        }
    }

    /**
     * register guild to website
     * 
     * @return void
     */
    private function _registerGuild() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addGuild($this->_formFields);
        $this->_assignGuildLogo(DBObjects::$insertId);
    }

    /**
     * assign guild logo image to guild if one is supplied, else assign default
     * 
     * @param  integer $guildId [ id of guild ]
     * 
     * @return void
     */
    private function _assignGuildLogo($guildId) {
        $imagePath        = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' . $guildId;
        $defaultImagePath = ABS_FOLD_SITE_LOGOS . 'guild_default_logo.png';

        if ( Functions::validateImage($this->_formFields->guildLogo) ) {
            move_uploaded_file($this->_formFields->guildLogo['tmp_name'], $imagePath);
        } else {
            copy($defaultImagePath, $imagePath);
        }
    }
}