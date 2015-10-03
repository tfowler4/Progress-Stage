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

        if ( Post::get('guild-id') || Post::get('submit') ) {
            switch ($this->_action) {
                case "add":
                    $this->addNewKill();
                    break;
                case "edit":
                    $this->editKill(Post::get('guild-id'));
                    break;
                case "remove":
                    $this->removeKill(Post::get('guild-id'));
                    break;
            }
        }

        die;
    }

    /**
     * insert new kill details in encounterkills_table
     *
     * @return void
     */
    public function addNewKill() {
        $this->_formFields   = new AdminKillSubmissionFormFields();
        $this->_formFields->guildId    = Post::get('guild-id');
        $this->_formFields->encounter  = Post::get('create-kill-encounter-name');
        $this->_formFields->dateMonth  = Post::get('create-kill-month');
        $this->_formFields->dateDay    = Post::get('create-kill-day');
        $this->_formFields->dateYear   = Post::get('create-kill-year');
        $this->_formFields->dateHour   = Post::get('create-kill-hour');
        $this->_formFields->dateMinute = Post::get('create-kill-minute');
        $this->_formFields->screenshot = Post::get('screenshot');
        $this->_formFields->videoTitle = Post::get('video-link-title');
        $this->_formFields->videoUrl   = Post::get('video-link-url');
        $this->_formFields->videoType  = Post::get('video-link-type');

        DBObjects::addKill($this->_formFields);

        if ( !empty($this->_formFields->screenshot['tmp_name']) ) {
            $imagePath = ABS_FOLD_KILLSHOTS . $this->_formFields->guildId . '-' . $this->_formFields->encounter;

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }

            move_uploaded_file($this->_formFields->screenshot['tmp_name'], $imagePath);
        }
    }

    /**
     * create html to prepare form and display guild and encounter name
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return string [ return html containing specified dungeon details ]
     */
    public function editKillSelectHtml($guildDetails) {
        $html = '';
        $html .= '<form class="admin-form kill edit" id="form-kill-edit" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-edit-kill-listing">';
        $html .= '<input hidden type="text" id="edit-kill-guild-id" name="guild-id" value="' . $guildDetails->_guildId . '"/>';
        $html .= '<tr><th>Encounter Name</th></tr>';
        $html .= '<tr><td><select id="kill-edit-encounter" name="edit-kill-encounter-id">';
        $html .= '<option value="">Select Encounter</option>';
            foreach ( (array)$guildDetails->_encounterDetails as $encounterId => $encounterDetails ):
                if ( isset($encounterDetails->_encounterId) ):
                    $html .= '<option value="' . $encounterDetails->_encounterId . '">' . $encounterDetails->_dungeon . '-' . $encounterDetails->_encounterName . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '</table>';
        $html .='</form>';

        return $html;
    }

    /**
     * create html to prepare form and display guild encounter kill details
     * 
     * @param  EncounterDetails $encounterDetails [ encounter details object ]
     * 
     * @return string [ return html containing specified dungeon details ]
     */
    public function editKillDetailsHtml($encounterDetails) {
        $videoArray = $encounterDetails['videos'];

        $date = explode('-', $encounterDetails['date']);
        $time = explode(':', $encounterDetails['time']);

        $html = '';
        $html .= '<form class="admin-form kill edit" id="form-kill-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-edit-kill-listing">';
        $html .= '<input hidden type="text" id="edit-kill-guild-id" name="guild-id" value="' . $encounterDetails['guild_id'] . '"/>';
        $html .= '<input hidden type="text" id="edit-kill-guild-id" name="edit-kill-encounter-id" value="' . $encounterDetails['encounter_id'] . '"/>';
        $html .= '<tr><th>Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="edit-kill-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == $date[1] ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="edit-kill-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $date[2] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="edit-kill-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $date[0] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Time</th></tr>';
        $html .= '<tr><td><select class="admin-select hour" name="edit-kill-hour">';
            foreach( CommonDataContainer::$hoursArray as $hour => $hourValue):
                if ( $hour == $time[0] ):
                    $html .= '<option value="' .$hour . '" selected>' . $hourValue .'</option>';
                else:
                    $html .= '<option value="' .$hour . '">' . $hourValue .'</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select minute" name="edit-kill-minute">';
            foreach( CommonDataContainer::$minutesArray as $minute => $minuteValue):
                if ( $minute == $time[1] ):
                    $html .= '<option value="' .$minute . '" selected>' .$minuteValue . '</option>';
                else:
                    $html .= '<option value="' .$minute . '">' .$minuteValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Screenshot</th></tr>';
        $html .= '<tr><td><input type="file" name="screenshot" /></td></tr>';
        $html .= '<tr><th>Video</th></tr>';
            foreach ($videoArray as $videoId => $videoDetails):
                if ( !empty($videoDetails['url']) )
                $html .= '<tr><td><input type="text" id="edit-kill-video-url" name="edit-kill-video-url" value="' . $videoDetails['url'] . '"/></td></tr>';
            endforeach;
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
        $html .= '<input id="admin-submit-kill-edit" type="submit" value="Submit" />';
        $html .='</form>';

        return $html;
    }

    /**
     * delete from encounterkills_table by specified id
     * 
     * @param string $guildId [ id of a specific guild]
     *
     * @return void
     */
    public function editKill($guildId) {
        $guildDetails = CommonDataContainer::$guildArray[$guildId];
        $this->getAllGuildDetails($guildDetails);
        $html = '';

        if ( Post::get('submit') ) {
            $guildId     = Post::get('guild-id');
            $encounterId = Post::get('edit-kill-encounter-id');
            $date        = Post::get('edit-kill-year') . '-' . Post::get('edit-kill-month') . '-' . Post::get('edit-kill-day');
            $time        = Post::get('edit-kill-hour') . ':' . Post::get('edit-kill-minute');
            $videoUrl    = Post::get('edit-kill-video-url');

            $query = $this->_dbh->prepare(sprintf(
                "UPDATE %s
                    SET date = '%s', 
                        time = '%s'
                  WHERE guild_id = '%s'
                    AND encounter_id = '%s'",
                DbFactory::TABLE_KILLS,
                $date,
                $time,
                $guildId,
                $encounterId
            ));
            $query->execute();
        } elseif ( !Post::get('edit-kill-encounter-id') ) {
            $html = $this->editKillSelectHtml($guildDetails);

            echo $html;
        } elseif ( Post::get('edit-kill-encounter-id') ) {
            $encounterId = Post::get('edit-kill-encounter-id');

            $html = $this->getEncounter($guildId, $encounterId);

            echo $html;
        }
    }

    /**
     * create html to prepare form and display guild and encounter name
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return string [ return html containing specified dungeon details ]
     */
    public function removeKillHtml($guildDetails) {
        $html = '';
        $html .= '<form class="admin-form kill remove" id="form-kill-remove" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-remove-kill-listing">';
        $html .= '<tr><td><input hidden type="text" name="guild-id" value="' . $guildDetails->_guildId . '"/></td></tr>';
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
            $guildId     = Post::get('guild-id');
            $encounterId = Post::get('remove-kill-encounter-id');

            $query = $this->_dbh->prepare(sprintf(
                "DELETE 
                   FROM %s
                  WHERE guild_id = '%s'
                    AND encounter_id = '%s'",
                DbFactory::TABLE_KILLS,
                $guildId,
                $encounterId
            ));
            $query->execute();

            $imagePath = ABS_FOLD_KILLSHOTS . $guildId . '-' . $encounterId;

            if ( file_exists($imagePath) ) {
                unlink($imagePath);
            }
        } else {
            $html         = '';
            $guildDetails = CommonDataContainer::$guildArray[$guildId];

            $this->getAllGuildDetails($guildDetails);

            $html = $this->removeKillHtml($guildDetails);

            echo $html;
        }
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

    /**
     * To get encounter kill details
     * 
     * @param  string $guildId     [ id of a specific guild ]
     * @param  string $encounterId [ id of a specific encounter ]
     * 
     * @return void
     */
    public function getEncounter($guildId, $encounterId) {
        $videoArray = array();
        $html = '';

            $query = $this->_dbh->query(sprintf(
                "SELECT video_id,
                        guild_id,
                        encounter_id,
                        url,
                        type,
                        notes
                   FROM %s
                  WHERE guild_id = '%s' and
                  encounter_id = '%s'",
                DbFactory::TABLE_VIDEOS,
                $guildId,
                $encounterId
            ));
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $videoId = $row['video_id'];
                $videoArray[$videoId] = $row;
            }

            $query = $this->_dbh->query(sprintf(
                "SELECT kill_id,
                        guild_id,
                        encounter_id,
                        date,
                        time
                   FROM %s
                  WHERE guild_id = '%s' and
                  encounter_id = '%s'",
                DbFactory::TABLE_KILLS,
                $guildId,
                $encounterId
            ));
            while ($row = $query->fetch(PDO::FETCH_ASSOC)) {
                $row['videos'] = $videoArray;
                $html = $this->editKillDetailsHtml($row);
            }

        echo $html;
    }
}