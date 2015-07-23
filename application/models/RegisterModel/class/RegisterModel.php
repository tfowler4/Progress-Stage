<?php
class RegisterModel extends Model {
    protected $_newestGuilds;
    protected $_formFields;
    protected $_registerType;
    protected $_dialogOptions;

    const PASSWORD_MINIMUM = 3;
    const PAGE_TITLE       = 'Registration';

    public function __construct($module, $params) {
        parent::__construct($module);

        $this->title = self::PAGE_TITLE;

        $this->loadFormFields();

        $this->_formFields = new RegisterFormFields();

        if ( Post::formActive() ) { 
            $this->getRegistrationType();

            if ( !empty($this->_registerType) ) { 
                $this->processForm($this->_registerType);
            } else {
                $this->_dialogOptions = array('title' => 'Error', 'message' => 'Please fill out the form!');
            }
        }

        $this->_newestGuilds = $this->getNewestGuilds();
    }

    public function getRegistrationType() {
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

    public function processForm($registerType) {
        switch($registerType) {
            case 'user':
                $this->registerUser();
                break;
            case 'guild':
                $this->registerUser();
                $this->registerGuild();
                break;
            default:
                break;
        }
    }

    public function getNewestGuilds() {
        $dbh        = DbFactory::getDbh();
        $dataArray  = array();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE progression != ''
           ORDER BY date_created DESC
              LIMIT 20", 
            DbFactory::TABLE_GUILDS
            ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $dataArray[$row['guild_id']] = new GuildDetails($row);
        }

        return $dataArray;
    }

    public function registerUser() {
        $this->_formFields->password = $this->encryptPasscode($this->_formFields->username, $this->_formFields->password);

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

    public function registerGuild() {
        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addGuild($this->_formFields);
        $this->assignGuildLogo(DBObjects::$insertId);
    }

    public function assignGuildLogo($guildId) {
        $imagePath        = ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/guilds/logos/logo-' . $guildId;
        $defaultImagePath = ABSOLUTE_PATH . '/public/images/' . GAME_NAME_1 . '/logos/site/guild_default_logo.png';

        if ( Functions::validateImage($this->_formFields->guildLogo) ) {
            move_uploaded_file($this->_formFields->guildLogo['tmp_name'], $imagePath);
        } else {
            copy($defaultImagePath, $imagePath);
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

    public function loadFormFields() {
        require 'RegisterFormFields.php';
    }
}