<?php

/**
 * register to the website page
 */
class RegisterModel extends Model {
    protected $_newestGuilds;
    protected $_formFields;
    protected $_registerType;
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

        if ( Post::formActive() ) { 
            $this->_getRegistrationType();

            if ( !empty($this->_registerType) ) { 
                $this->_processForm($this->_registerType);
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'Please fill out the form!');
            }
        }

        $this->_newestGuilds = $this->_getNewestGuilds();
    }

    /**
     * get registration type by determining if specific fields were completed
     * 
     * @return void
     */
    private function _getRegistrationType() {
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

        if ( !empty($this->_formFields->username) 
             && !empty($this->_formFields->email)
             && !empty($this->_formFields->password)
             && !empty($this->_formFields->retypePassword)
             && $this->_formFields->isImportant == 'on'
             && ($this->_formFields->password ==$this->_formFields->retypePassword ) ) {
                $this->_registerType = 'user';
        }

        if ( $this->_registerType == 'user' 
             && !empty($this->_formFields->guildName)
             && !empty($this->_formFields->faction)
             && !empty($this->_formFields->server) 
             && !empty($this->_formFields->country)
             && $this->_formFields->isImportant == 'on' ) {
                $this->_registerType = 'guild';
        }
    }

    /**
     * process submitted register form depending on register type
     * 
     * @return void
     */
    private function _processForm($registerType) {
        switch($registerType) {
            case 'user':
                $this->_registerUser();
                break;
            case 'guild':
                $this->_registerUser();
                $this->_registerGuild();
                break;
            default:
                break;
        }
    }

    /**
     * get the newest guilds registered
     * 
     * @return array [ array of guildd ]
     */
    private function _getNewestGuilds() {
        $dbh        = DbFactory::getDbh();
        $dataArray  = array();

        $query = $dbh->prepare(sprintf(
            "SELECT *
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
     * @return return void
     */
    private function _registerUser() {
        $this->_formFields->password = $this->_encryptPasscode($this->_formFields->password);

        DBObjects::addUser($this->_formFields);

        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "SELECT *
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
     * @return return void
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
}