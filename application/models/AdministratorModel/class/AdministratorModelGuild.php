<?php

/**
 * guild administration
 */
class AdministratorModelGuild {
    protected $_action;
    protected $_dbh;
    protected $_formFields;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('adminpanel-guild') || Post::get('submit') ) {
            $this->populateFormFields();

            switch ($this->_action) {
                case "add":
                    $this->addNewGuild();
                    break;
                case "edit":
                    $this->editGuild(Post::get('adminpanel-guild'));
                    break;
                case "remove":
                    $this->removeGuild();
                    break;
            }
        }

        die;
    }

    public function populateFormFields() {
        $this->_formFields = new AdminGuildFormFields();

        $this->_formFields->guildId     = Post::get('adminpanel-guild-id');
        $this->_formFields->guild       = Post::get('adminpanel-guild');
        $this->_formFields->faction     = Post::get('adminpanel-faction');
        $this->_formFields->server      = Post::get('adminpanel-server');
        $this->_formFields->country     = Post::get('adminpanel-country');
        $this->_formFields->guildLeader = Post::get('adminpanel-guild-leader');
        $this->_formFields->website     = Post::get('adminpanel-website');
        $this->_formFields->facebook    = Post::get('adminpanel-facebook');
        $this->_formFields->twitter     = Post::get('adminpanel-twitter');
        $this->_formFields->guildLogo   = Post::get('adminpanel-guild-logo');
        $this->_formFields->active      = Post::get('adminpanel-active');
    }

    /**
     * insert new guild details into the database
     *
     * @return void
     */
    public function addNewGuild() {
        $query = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (name, server, country)
            values('%s', '%s', '%s')",
            DbFactory::TABLE_GUILDS,
            $this->_formFields->guild,
            $this->_formFields->server,
            $this->_formFields->country
        ));

        $query->execute();
    }

    /**
     * create html to prepare form and display all necessary guild details
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return string                     [ return html containing specified dungeon details ]
     */
    public function editGuildHtml($guildDetails) {
        $html = '';
        $html .= '<form class="admin-form guild edit details" id="form-guild-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-guild-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<input hidden type="text" name="adminpanel-guild-id" value="' . $guildDetails->_guildId . '"/>';
        $html .= '<tr><th>Date Created</th></tr>';
        $html .= '<tr><td>' . $guildDetails->_dateCreated . '</td></tr>';
        $html .= '<tr><th>Guild Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-guild" value="' . $guildDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Leader</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-guild-leader" value="' . $guildDetails->_leader . '"/></td></tr>';
        $html .= '<tr><th>Website</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-website" value="' . $guildDetails->_website . '"/></td></tr>';
        $html .= '<tr><th>Facebook</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-facebook" value="' . $guildDetails->_facebook . '"/></td></tr>';
        $html .= '<tr><th>Twitter</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-twitter" value="' . $guildDetails->_twitter . '"/></td></tr>';
        $html .= '<tr><th>Faction</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-faction" value="' . $guildDetails->_faction . '"/></td></tr>';
        $html .= '<tr><th>Server</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-server" value="' . $guildDetails->_server . '"/></td></tr>';
        $html .= '<tr><th>Active</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-active" value="' . $guildDetails->_active . '"/></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
        $html .= '<input id="admin-submit-guild-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }

    /**
     * get id from drop down selection to obtain the specific guild details
     * and pass that array to editGuildHtml to display
     * 
     * @param  string $guildId [ id of a specific guild ]
     * 
     * @return void
     */
    public function editGuild($guildId) {
        // if the submit field is present, update guild data
        if ( Post::get('submit') ) {
            $query = $this->_dbh->prepare(sprintf(
                "UPDATE %s
                SET leader = '%s', 
                    website = '%s',  
                    facebook = '%s',  
                    twitter = '%s',  
                    faction = '%s',  
                    server = '%s',  
                    active = '%s'
                WHERE guild_id = '%s'",
                DbFactory::TABLE_GUILDS,
                $this->_formFields->guildLeader,
                $this->_formFields->website,
                $this->_formFields->facebook,
                $this->_formFields->twitter,
                $this->_formFields->faction,
                $this->_formFields->server,
                $this->_formFields->active,
                $this->_formFields->guildId
            ));
            $query->execute();
        } else {
            $html         = '';
            $guildDetails = CommonDataContainer::$guildArray[$guildId];

            $html = $this->editGuildHtml($guildDetails);
            
            echo $html;
        }
    }

    /**
     * delete from guild_table by specified id
     * 
     * @return void
     */
    public function removeGuild() {
        $query = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE guild_id = '%s'",
            DbFactory::TABLE_GUILDS,
            $this->_formFields->guild
        ));
        $query->execute();
    }
}