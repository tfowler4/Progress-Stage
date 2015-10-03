<?php

/**
 * kill administration
 */
class AdministratorModelKill {
    protected $_action;
    protected $_dbh;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('remove-kill-guild-id') || Post::get('submit') ) {
            switch ($this->_action) {
                case "add":
                    $this->addNewKill();
                    break;
                case "remove":
                    $this->removeKill(Post::get('remove-kill-guild-id'));
                    break;
            }
        } else {
            die;
        }
    }

    /**
     * insert new kill details in encounterkills_table
     *
     * @return void
     */
    public function addNewKill() {
        $this->_formFields   = new AdminKillSubmissionFormFields();
        $this->_formFields->guildId    = Post::get('create-kill-guild-name');
        $this->_formFields->encounter  = Post::get('create-kill-encounter-name');
        $this->_formFields->dateMonth  = Post::get('create-kill-month');
        $this->_formFields->dateDay    = Post::get('create-kill-day');
        $this->_formFields->dateYear   = Post::get('create-kill-year');
        $this->_formFields->dateHour   = Post::get('create-kill-hour');
        $this->_formFields->dateMinute = Post::get('create-kill-minute');
        $this->_formFields->screenshot = Post::get('create-kill-screenshot');
        $this->_formFields->videoTitle = Post::get('video-link-title');
        $this->_formFields->videoUrl   = Post::get('video-link-url');
        $this->_formFields->videoType  = Post::get('video-link-type');

        //DBObjects::addKill($this->_formFields);
    }

    /**
     * create html to prepare form and display guild and encounter name
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return string                     [ return html containing specified dungeon details ]
     */
    public function removeKillHtml($guildDetails) {
        $html = '';
        $html .= '<form class="admin-form kill remove" id="form-kill-remove" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-remove-kill-listing">';
        $html .= '<tr><td><input hidden type="text" name="remove-kill-guild-id" value="' . $guildDetails->_guildId . '"/></td></tr>';
        $html .= '<tr><th>Encounter Name</th></tr>';
        $html .= '<tr><td><select name="remove-kill-encounter-id">';
        $html .= '<option value="">Select Encounter</option>';
            foreach ( (array)$guildDetails->_encounterDetails as $encounterId => $encounterDetails ):
                if ( isset($encounterDetails->_encounterId) ):
                    $html .= '<option value="' . $encounterDetails->_encounterId . '">' . $encounterDetails->_dungeon . '-' . $encounterDetails->_encounterName . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
        $html .= '<input id="admin-submit-kill-remove" type="submit" value="Remove" />';
        $html .='</form>';

        return $html;
    }

    /**
     * delete from encounterkills_table by specified id
     * 
     * @return void
     */
    public function removeKill($guildId) {
        // if the submit field is present, remove kill data
        if ( Post::get('submit') ) {
            $guildId     = Post::get('remove-kill-guild-id');
            $encounterId = Post::get('remove-kill-encounter-id');

            $query = $this->_dbh->prepare(sprintf(
                "DELETE 
                   FROM %s
                  WHERE guild_id = '%s' and
                  encounter_id = '%s'",
                DbFactory::TABLE_KILLS,
                $guildId,
                $encounterId
                ));
            $query->execute();
        } else {
            $html = '';
            $guildDetails = CommonDataContainer::$guildArray[$guildId];

            $this->getAllGuildDetails($guildDetails);

            $html = $this->removeKillHtml($guildDetails);

            echo $html;
        }

        die;
    }

    /**
     * generate all encounter standings and rankings information
     * 
     * @return void
     */
    public function getAllGuildDetails($guildDetails) {
        $guildDetails->generateRankDetails('encounters');

        $dbh       = DbFactory::getDbh();
        $dataArray = array();

        $query = $dbh->prepare(sprintf(
            "SELECT kill_id,
                    guild_id,
                    encounter_id,
                    dungeon_id,
                    tier,
                    raid_size,
                    datetime,
                    date,
                    time,
                    time_zone,
                    server,
                    videos,
                    server_rank,
                    region_rank,
                    world_rank,
                    country_rank
               FROM %s
              WHERE guild_id=%d", 
                    DbFactory::TABLE_KILLS, 
                    $guildDetails->_guildId
                ));
        $query->execute();

        while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
            $encounterId         = $row['encounter_id'];
            $encounterDetails    = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonId           = $encounterDetails->_dungeonId;
            $dungeonDetails      = CommonDataContainer::$dungeonArray[$dungeonId];
            $tierId              = $dungeonDetails->_tier;
            $tierDetails         = CommonDataContainer::$tierArray[$tierId];

            $arr = $guildDetails->_progression;
            $arr['dungeon'][$dungeonId][$encounterId] = $row;
            $arr['encounter'][$encounterId] = $row;
            $guildDetails->_progression = $arr;
        }

        $guildDetails->generateEncounterDetails('');
    }
}