<?php

/**
 * guild administration
 */
class AdministratorModelGuild {
    protected $_action;
    protected $_dbh;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('guild') || Post::get('submit') ) {
            switch ($this->_action) {
                case "add":
                    $this->addNewGuild();
                    break;
                case "edit":
                    $this->editGuild(Post::get('guild'));
                    break;
                case "remove":
                    $this->removeGuild();
                    break;
            }
        } else {
            die;
        }
    }

    /**
     * insert new guild details into the database
     *
     * @return void
     */
    public function addNewGuild() {
        $guild   = Post::get('create-guild-name');
        $server  = Post::get('create-guild-server');
        $country = Post::get('create-guild-country');

        $query = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (name, server, country)
            values('%s', '%s', '%s')",
            DbFactory::TABLE_GUILDS,
            $guild,
            $server,
            $country
            ));
        $query->execute();
        die;
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
        $html .= '<tr><td><input hidden type="text" name="edit-guild-id" value="' . $guildDetails->_guildId . '"/></td></tr>';
        $html .= '<tr><th>Date Created</th></tr>';
        $html .= '<tr><td>' . $guildDetails->_dateCreated . '</td></tr>';
        $html .= '<tr><th>Leader</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-guild-leader" value="' . $guildDetails->_leader . '"/></td></tr>';
        $html .= '<tr><th>Website</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-guild-website" value="' . $guildDetails->_website . '"/></td></tr>';
        $html .= '<tr><th>Facebook</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-guild-facebook" value="' . $guildDetails->_facebook . '"/></td></tr>';
        $html .= '<tr><th>Twitter</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-guild-twitter" value="' . $guildDetails->_twitter . '"/></td></tr>';
        $html .= '<tr><th>Faction</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-guild-faction" value="' . $guildDetails->_faction . '"/></td></tr>';
        $html .= '<tr><th>Server</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-guild-server" value="' . $guildDetails->_server . '"/></td></tr>';
        $html .= '<tr><th>Active</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-guild-active" value="' . $guildDetails->_active . '"/></td></tr>';
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
            $guildId  = Post::get('edit-guild-id');
            $leader   = Post::get('edit-guild-leader');
            $website  = Post::get('edit-guild-website');
            $facebook = Post::get('edit-guild-facebook');
            $twitter  = Post::get('edit-guild-twitter');
            $google   = Post::get('edit-guild-google');
            $faction  = Post::get('edit-guild-faction');
            $server   = Post::get('edit-guild-server');
            $active   = Post::get('edit-guild-active');

            $query = $this->_dbh->prepare(sprintf(
                "UPDATE %s
                SET leader = '%s', 
                    website = '%s',  
                    facebook = '%s',  
                    twitter = '%s',  
                    google = '%s',  
                    faction = '%s',  
                    server = '%s',  
                    active = '%s'
                WHERE guild_id = '%s'",
                DbFactory::TABLE_GUILDS,
                $leader,
                $website,
                $facebook,
                $twitter,
                $google,
                $faction,
                $server,
                $active,
                $guildId
                ));
            $query->execute();
        } else {
            $html         = '';
            $guildDetails = CommonDataContainer::$guildArray[$guildId];

            $html = $this->editGuildHtml($guildDetails);
            
            echo $html;
        }

        die;
    }

    /**
     * delete from guild_table by specified id
     * 
     * @return void
     */
    public function removeGuild() {
        $guildId = Post::get('remove-guild-id');

        $query = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE guild_id = '%s'",
            DbFactory::TABLE_GUILDS,
            $guildId
            ));
        $query->execute();
        die;
    }
}