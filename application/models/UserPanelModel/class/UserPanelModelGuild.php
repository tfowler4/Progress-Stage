<?php

/**
 * user control panal page for guild administration
 */
class UserPanelModelGuild extends UserPanelModel {
    protected $_action;
    protected $_formFields;
    protected $_dialogOptions;
    protected $_guildDetails;
    protected $_userDetails;
    protected $_userGuilds;
    protected $_raidTeams;

    const GUILD_ADD       = 'add';
    const GUILD_EDIT      = 'edit';
    const GUILD_RAID_TEAM = 'raid-team';
    const GUILD_REMOVE    = 'remove';

    const MAX_GUILDS      = 3;
    const MAX_RAID_TEAMS  = 3;

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
            'Status'          => '_activeStatus'
        );

    public function __construct($action, $formFields, $guildDetails, $userDetails, $userGuilds, $raidTeams) {
        $this->_guildDetails = $guildDetails;
        $this->_userDetails  = $userDetails;
        $this->_userGuilds   = $userGuilds;
        $this->_action       = $action;
        $this->_formFields   = $formFields;
        $this->_raidTeams    = $raidTeams;

        if ( Post::formActive() ) {
            $this->_populateFormFields();

            switch($this->_action) {
                case self::GUILD_ADD:
                    FormValidator::validate('guild-add', $this->_formFields);
                    break;
                case self::GUILD_REMOVE:
                    FormValidator::validate('guild-remove', $this->_formFields);
                    break;
                case self::GUILD_EDIT:
                    FormValidator::validate('guild-edit', $this->_formFields);
                    break;
                case self::GUILD_RAID_TEAM:
                    FormValidator::validate('guild-add-raid-team', $this->_formFields);
                    break;
            }

            if ( FormValidator::$isFormInvalid ) {
                $this->_dialogOptions = array('title' => 'Error', 'message' => FormValidator::$message);
                return;
            }

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
        }
    }

    /**
     * process submitted guild form
     * 
     * @return void
     */
    private function _populateFormFields() {
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
    }

    /**
     * update guild in database
     * 
     * @return void
     */
    private function _editGuild() {
        if ( !isset($this->_guildDetails) || $this->_guildDetails == null ) { header('Location: ' . HOST_NAME . '/userpanel'); die(); }

        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::editGuild($this->_formFields, $this->_guildDetails);
        if ( !empty($this->_formFields->guildLogo['tmp_name']) ) { $this->_assignGuildLogo($this->_guildDetails->_guildId); }

        $this->_guildDetails = $this->_getUpdatedGuildDetails($this->_guildDetails->_guildId);

        $this->_dialogOptions = array('title' => 'Success', 'message' => 'You have successfully updated your guild details!');
    }

    /**
     * remove guild in database
     * 
     * @return void
     */
    private function _removeGuild() {
        if ( !isset($this->_guildDetails) || $this->_guildDetails == null ) { header('Location: ' . HOST_NAME . '/userpanel');  die(); }

        DBObjects::removeGuild($this->_formFields);

        // Guild is a child of a parent guild, update parent's info
        if ( !empty($this->_guildDetails->_parent) && $this->_guildDetails->_parent != '0' ) {
            $parentId    = $this->_guildDetails->_parent;
            $parentGuild = CommonDataContainer::$guildArray[$parentId];

            $parentGuildChildren = $parentGuild->_child;

            $childrenIdArray = explode('||', $parentGuildChildren);

            foreach( $childrenIdArray as $index => $guildId ) {
                if ( $childrenIdArray[$index] == $this->_guildDetails->_guildId ) { unset($childrenIdArray[$index]); } 
            }

            $sqlChild = '';

            if ( !empty($childrenIdArray) ) {
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

        $this->_dialogOptions = array('title' => 'Success', 'message' => 'You have successfully removed the guild ' . $this->_guildDetails->_name . '!');

        unset($this->_guildDetails);
    }

    /**
     * add guild into database
     * 
     * @return void
     */
    private function _addGuild() {
        if ( count($this->_userGuilds) >= self::MAX_GUILDS ) { header('Location: ' . HOST_NAME . '/userpanel'); die(); }

        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addGuild($this->_formFields);
        $this->_assignGuildLogo(DBObjects::$insertId);

        $this->_dialogOptions = array('title' => 'Success', 'message' => 'You have successfully created a new guild!');
    }

    /**
     * add guild into database
     * 
     * @return void
     */
    private function _addRaidTeam() {
        $guildId = $this->_guildDetails->_guildId;

        if ( count($this->_raidTeams[$guildId]) >= self::MAX_RAID_TEAMS ) { header('Location: ' . HOST_NAME . '/userpanel'); die(); }

        $this->_formFields->region = CommonDataContainer::$serverArray[$this->_formFields->server]->_region;

        DBObjects::addChildGuild($this->_formFields, $this->_userDetails->_userId, $this->_guildDetails);
        $this->_copyParentGuildLogo($this->_guildDetails->_guildId, DBObjects::$insertId);

        $this->_dialogOptions = array('title' => 'Success', 'message' => 'You have successfully added a new raid team!');
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
     * copy the parent guild logo onto the child guild logo
     * 
     * @return void
     */
    private function _copyParentGuildLogo($parentId, $childId) {
        $parentPath = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' . $parentId;
        $childPath  = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' . $childId;

        copy($parentPath, $childPath);
    }
}