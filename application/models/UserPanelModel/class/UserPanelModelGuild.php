<?php
class UserPanelModelGuild extends UserPanelModel {
    protected $_guildDetails;

    const GUILD_ADD       = 'add';
    const GUILD_EDIT      = 'edit';
    const GUILD_RAID_TEAM = 'raid-team';
    const GUILD_REMOVE    = 'remove';

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

    public function __construct($guildDetails) {
        $this->_guildDetails = $guildDetails;
        

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