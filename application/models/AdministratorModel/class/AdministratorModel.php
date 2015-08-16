<?php 

class AdministratorModel extends Model {
    protected $_userDetails;

    public function __construct($module, $params) {
        parent::__construct($module);

        if (isset($_SESSION['userDetails']) ) {
            $this->_userDetails = $_SESSION['userDetails'];
            
            if ($this->_userDetails->_admin != 1) {
                header('Location: ' . HOST_NAME);
            }
        } else {
            header('Location: ' . HOST_NAME);
        }

        if (isset($_POST['request']) ) {

            switch ($_POST['request']) {
                case "tier-add":
                    $this->addNewTier();
                    break;
                case "tier-edit":
                    $this->editTier($_POST['tier']);
                    break;
                case "tier-edit-details":
                    $this->editTierDetails();
                    break;
                case "dungeon-add":
                    $this->addNewDungeon();
                    break;
                case "encounter-add":
                    $this->addNewEncounter();
                    break;
                case "guild-add":
                    $this->addNewGuild();
                    break;
                case "guild-remove":
                    $this->removeGuild();
                    break;
                case "guild-edit":
                    $this->editGuild($_POST['guild']);
                    break;
                case "guild-edit-details":
                    $this->editGuildDetails();
                    break;
            }
        }
    }

    public function addNewTier() {
        $tier      = $_POST['form'][0]['value'];
        $altName   = $_POST['form'][1]['value'];
        $tierName  = $_POST['form'][2]['value'];
        $startDate = $_POST['form'][5]['value'] . '-' . $_POST['form'][3]['value'] . '-' .$_POST['form'][4]['value'];

        $sqlString = sprintf(
            "INSERT INTO %s
            (tier, alt_tier, title, date_start)
            values('%s', '%s', '%s', '%s')",
            DbFactory::TABLE_TIERS,
            $tier,
            $altName,
            $tierName,
            $startDate
            );
        die;
    }

    public function editTierHthml($tierDetails) {
        $startDate = explode(' ', $tierDetails->_dateStart);
        $endDate   = explode(' ', $tierDetails->_dateEnd);

        $html = '';
        $html .= '<form class="admin-form tier edit details" id="form-tier-edit-details" method="POST" action="http://localhost/stage/administrator">';
        $html .= '<table class="admin-tier-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td><input hidden type="text" name="text-tier-id" value="' . $tierDetails->_tierId . '"/></td></tr>';
        $html .= '<tr><th>Tier</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-tier" value="' . $tierDetails->_tier . '"/></td></tr>';
        $html .= '<tr><th>Alt Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-alt-tier" value="' . $tierDetails->_altTier . '"/></td></tr>';
        $html .= '<tr><th>Start Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="select-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == date('m', strtotime($tierDetails->_dateStart)) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="select-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $startDate[1] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="select-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $startDate[2] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>End Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="select-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == date('m', strtotime($tierDetails->_dateEnd)) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="select-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $endDate[1] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="select-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $endDate[2] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Title</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-title" value="' . $tierDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Alt Title</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-alt-title" value="' . $tierDetails->_altTitle . '"/></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<input id="admin-submit-tier-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }

    public function editTier($tierId) {
        $html         = '';
        $tierDetails = CommonDataContainer::$tierArray[$tierId];

        $html = $this->editTierHthml($tierDetails);

        echo $html;
        die;
    }

    public function editTierDetails() {
        $tierId    = $_POST['form'][0]['value'];
        $tier      = $_POST['form'][1]['value'];
        $altTier   = $_POST['form'][2]['value'];
        $startDate = $_POST['form'][5]['value'] . '-' . $_POST['form'][3]['value'] . '-' .$_POST['form'][4]['value'];
        $endDate   = $_POST['form'][8]['value'] . '-' . $_POST['form'][6]['value'] . '-' .$_POST['form'][7]['value'];
        $title     = $_POST['form'][9]['value'];
        $altTitle  = $_POST['form'][10]['value'];

        $sqlString = sprintf(
            "UPDATE %s
            SET tier = '%s', alt_tier = '%s', date_start = '%s', date_end = '%s', title = '%s', alt_title = '%s'
            WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $tier,
            $altTier,
            $startDate,
            $endDate,
            $title,
            $altTitle,
            $tierId
            );
        print_r($sqlString);
        die;
    }
    public function addNewDungeon() {
        $dungeon   = $_POST['form'][0]['value'];
        $tier      = $_POST['form'][1]['value'];
        $numOfMobs = $_POST['form'][2]['value'];

        $sqlString = sprintf(
            "INSERT INTO %s
            (name, tier, mobs)
            values('%s', '%s', '%s')",
            DbFactory::TABLE_DUNGEONS,
            $dungeon,
            $tier,
            $numOfMobs
            );
        die;
    }

    public function addNewEncounter() {
        $encounter = $_POST['form'][0]['value'];
        $dungeon   = $_POST['form'][1]['value'];
        $tier      = $_POST['form'][2]['value'];
        $raidSize  = $_POST['form'][3]['value'];

        $sqlString = sprintf(
            "INSERT INTO %s
            (name, dungeon, tier, players)
            values('%s', '%s', '%s', '%s')",
            DbFactory::TABLE_ENCOUNTERS,
            $encounter,
            $dungeon,
            $tier,
            $raidSize
            );
        die;
    }

    public function addNewGuild() {
        $guild   = $_POST['form'][0]['value'];
        $server  = $_POST['form'][1]['value'];
        $country = $_POST['form'][2]['value'];

        $sqlString = sprintf(
            "INSERT INTO %s
            (name, server, country)
            values('%s', '%s', '%s')",
            DbFactory::TABLE_GUILDS,
            $guild,
            $server,
            $country
            );
        die;
    }

    public function removeGuild() {
        $guildId = $_POST['form'][0]['value'];

        $sqlString = sprintf(
            "DELETE 
               FROM %s
              WHERE guild_id = '%s'",
            DbFactory::TABLE_GUILDS,
            $guildId
            );
        die;
    }

    public function editGuildHtml($guildDetails) {
        $html = '';
        $html .= '<form class="admin-form guild edit details" id="form-guild-edit-details" method="POST" action="http://localhost/stage/administrator">';
        $html .= '<table class="admin-guild-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td><input hidden type="text" name="text-guild-id" value="' . $guildDetails->_guildId . '"/></td></tr>';
        $html .= '<tr><th>Date Created</th></tr>';
        $html .= '<tr><td>' . $guildDetails->_dateCreated . '</td></tr>';
        $html .= '<tr><th>Leader</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-leader" value="' . $guildDetails->_leader . '"/></td></tr>';
        $html .= '<tr><th>Website</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-website" value="' . $guildDetails->_website . '"/></td></tr>';
        $html .= '<tr><th>Facebook</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-facebook" value="' . $guildDetails->_facebook . '"/></td></tr>';
        $html .= '<tr><th>Twitter</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-twitter" value="' . $guildDetails->_twitter . '"/></td></tr>';
        $html .= '<tr><th>Google</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-google" value="' . $guildDetails->_google . '"/></td></tr>';
        $html .= '<tr><th>Faction</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-faction" value="' . $guildDetails->_faction . '"/></td></tr>';
        $html .= '<tr><th>Server</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-server" value="' . $guildDetails->_server . '"/></td></tr>';
        $html .= '<tr><th>Active</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-guild-active" value="' . $guildDetails->_active . '"/></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<input id="admin-submit-guild-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }

    public function editGuild($guildId) {
        $html         = '';
        $guildDetails = CommonDataContainer::$guildArray[$guildId];

        $html = $this->editGuildHtml($guildDetails);
        
        echo $html;
        die;
    }

    public function editGuildDetails() {
        $guildId  = $_POST['form'][0]['value'];
        $leader   = $_POST['form'][1]['value'];
        $website  = $_POST['form'][2]['value'];
        $facebook = $_POST['form'][3]['value'];
        $twitter  = $_POST['form'][4]['value'];
        $google   = $_POST['form'][5]['value'];
        $faction  = $_POST['form'][6]['value'];
        $server   = $_POST['form'][7]['value'];
        $active   = $_POST['form'][8]['value'];

        $sqlString = sprintf(
            "UPDATE %s
            SET leader = '%s', website = '%s',  facebook = '%s',  twitter = '%s',  google = '%s',  faction = '%s',  server = '%s',  active = '%s'
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
            );
        die;
    }
}

?>