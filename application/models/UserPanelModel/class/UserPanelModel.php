<?php
class UserPanelModel extends Model {
    protected $_userDetails;
    protected $_userGuilds;
    protected $_guildDetails;
    protected $_raidTeams;
    protected $_action;
    protected $_formFields;
    protected $_numOfGuilds = 0;
    protected $_validForm = false;
    protected $_dialogOptions;
    protected $_currentPanel;

    public $subModule;

    const MAX_GUILDS      = 3;
    const MAX_RAID_TEAMS  = 3;

    const SUB_GUILD = 'guild';
    const SUB_USER  = 'user';
    const SUB_KILLS = 'kills';

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
            if ( isset($params[0]) ) { $this->subModule     = strtolower($params[0]); }
            if ( isset($params[1]) ) { $this->_action       = strtolower($params[1]); }
            if ( isset($params[2]) ) { $this->_guildDetails = $this->_getCorrectGuild($params[2]); }
            if ( isset($params[3]) ) {
                if ( isset($this->_guildDetails->_encounterDetails->{$params[3]}) ) {
                    $this->_encounterDetails = $this->_guildDetails->_encounterDetails->{$params[3]};
                }
            }
        }

        $pathToCP = HOST_NAME . '/userpanel';

        switch($this->subModule) {
            case self::SUB_GUILD:
                $this->_formFields   = new GuildFormFields();
                $this->_currentPanel = new UserPanelModelGuild($this->_action, $this->_formFields, $this->_guildDetails);
                break;
            case self::SUB_USER:
                $this->_formFields   = new UserFormFields();
                $this->_currentPanel = new UserPanelModelUser($this->_action, $this->_formFields, $this->_userDetails);
                break;
            case self::SUB_KILLS:
                $this->_formFields   = new KillSubmissionFormFields();
                $this->_currentPanel = new UserPanelModelKill($this->_action, $this->_formFields, $this->_guildDetails);
                break;
        }
    }

    /**
     * get updated user details after submitting update form successfully
     * 
     * @param  integer $userId [ id of user ]
     * 
     * @return User [ user data object ]
     */
    protected function _getUpdatedUserDetails($userId) {
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
     * get child raid teams of a guild
     * 
     * @param  integer      $guildId      [ id of guild ]
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return array [ array of raid teams ]
     */
    protected function _getRaidTeams($guildId, $guildDetails) {
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
    protected function _getCorrectGuild($guildId) {
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
     * get list of guilds based on user logged in
     * 
     * @param  integer $userId [ id of user ]
     * 
     * @return array [ array of guilds ]
     */
    protected function _getUserGuilds($userId) {
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