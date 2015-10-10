<?php

/**
 * user control panal page
 */
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

        if ( !isset($this->_userDetails) ) { Functions::sendTo404(); }

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

        switch($this->subModule) {
            case self::SUB_GUILD:
                if ( is_numeric($this->_action) ) {
                    $this->_guildDetails = $this->_getCorrectGuild($this->_action);
                }

                if ( $this->_guildDetails == null ) {
                    header('Location: ' . HOST_NAME);
                    die;
                }

                $this->_formFields   = new GuildFormFields();
                $this->_currentPanel = new UserPanelModelGuild($this->_action, $this->_formFields, $this->_guildDetails, $this->_userDetails, $this->_userGuilds, $this->_raidTeams);
                break;
            case self::SUB_USER:
                $this->_formFields   = new UserFormFields();
                $this->_currentPanel = new UserPanelModelUser($this->_action, $this->_formFields, $this->_userDetails);
                break;
            case self::SUB_KILLS:
                if ( $this->_guildDetails == null ) {
                    header('Location: ' . HOST_NAME);
                    die;
                }

                $this->_formFields   = new KillSubmissionFormFields();
                $this->_currentPanel = new UserPanelModelKill($this->_action, $this->_formFields, $this->_guildDetails, $this->_encounterDetails);
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
     * get updated guild details after submitting update form successfully
     * 
     * @param  integer $guildId [ id of guild ]
     * 
     * @return User [ user data object ]
     */
    protected function _getUpdatedGuildDetails($guildId) {
        $dbh = DbFactory::getDbh();

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
              WHERE guild_id='%s'",
             DbFactory::TABLE_GUILDS,
             $guildId
             ));
        $query->execute();

        while ( $guild = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildDetails = new GuildDetails($guild);

            if ( isset($guildDetails) ) {
                $guildDetails = Functions::getAllGuildDetails($guildDetails);
            }

            return $guildDetails;
        }
    }

    /**
     * get updated encounter details after submitting update form successfully
     * 
     * @param  integer $guildId [ id of guild ]
     * 
     * @return User [ user data object ]
     */
    protected function _getUpdatedEncounterDetails($guildId, $encounterId) {
        $dbh = DbFactory::getDbh();
        $guildDetails;

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
              WHERE guild_id='%s'",
             DbFactory::TABLE_GUILDS,
             $guildId
             ));
        $query->execute();

        while ( $guild = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildDetails = new GuildDetails($guild);
            break;
        }

        if ( isset($guildDetails) ) {
            $guildDetails = Functions::getAllGuildDetails($guildDetails);
        }

        return $guildDetails->_encounterDetails->$encounterId;
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
        if ( !isset(CommonDataContainer::$guildArray[$guildId]) ) { return null; }

        $guildDetails = CommonDataContainer::$guildArray[$guildId];

        if ( !empty($guildDetails) && $guildDetails->_creatorId == $this->_userDetails->_userId ) {
            $this->_raidTeams[$guildId] = $this->_getRaidTeams($guildId, $guildDetails);
        }

        if ( isset($guildDetails) ) {
            $guildDetails = Functions::getAllGuildDetails($guildDetails);
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
            "SELECT guild_id,
                    name,
                    date_created,
                    leader,
                    website,
                    guild_type,
                    schedule,
                    facebook,
                    twitter,
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
              WHERE creator_id='%s'
                AND type='0'", 
                    DbFactory::TABLE_GUILDS, 
                    $userId));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildDetails                 = new GuildDetails($row);
            $guildArray[$row['guild_id']] = $guildDetails;

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
        $fileName = $this->subModule;

        if ( !empty($this->_action) ) {
            $fileName .= '-' . $this->_action;
        }

        $htmlFilePath = ABSOLUTE_PATH . '/application/models/UserPanelModel/html/' . $fileName . '.html';

        if ( file_exists($htmlFilePath) ) {
            include $htmlFilePath;
        } else {
            Functions::sendTo404();
        }
    }
}