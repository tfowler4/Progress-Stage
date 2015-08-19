<?php 

class AdministratorModel extends Model {
    protected $_userDetails;

    const PAGE_TITLE = 'Administrator Control Panel';

    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

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
                case "tier-remove":
                    $this->removeTier();
                    break;
                case "dungeon-add":
                    $this->addNewDungeon();
                    break;
                case "dungeon-edit":
                    $this->editDungeon($_POST['dungeon']);
                    break;
                case "dungeon-edit-details":
                    $this->editDungeonDetails();
                    break;
                case "dungeon-remove":
                    $this->removeDungeon();
                    break;
                case "encounter-add":
                    $this->addNewEncounter();
                    break;
                case "encounter-edit":
                    $this->editEncounter($_POST['encounter']);
                    break;
                case "encounter-edit-details":
                    $this->editEncounterDetails();
                    break;
                case "encounter-remove":
                    $this->removeEncounter();
                    break;
                case "guild-add":
                    $this->addNewGuild();
                    break;
                case "guild-edit":
                    $this->editGuild($_POST['guild']);
                    break;
                case "guild-edit-details":
                    $this->editGuildDetails();
                    break;
                case "guild-remove":
                    $this->removeGuild();
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

    public function editTierHtml($tierDetails) {
        $startDate = explode(' ', $tierDetails->_dateStart);
        $endDate   = explode(' ', $tierDetails->_dateEnd);

        $html = '';
        $html .= '<form class="admin-form tier edit details" id="form-tier-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
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
                if ( ($tierDetails->_dateEnd != 'Currently Active') && ($month == date('m', strtotime($tierDetails->_dateEnd))) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="select-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( ($tierDetails->_dateEnd != 'Currently Active') && ($day == $endDate[1]) ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="select-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( ($tierDetails->_dateEnd != 'Currently Active') && ($year == $endDate[2]) ):
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
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }

    public function editTier($tierId) {
        $html        = '';
        $tierDetails = CommonDataContainer::$tierArray[$tierId];

        $html = $this->editTierHtml($tierDetails);

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
        die;
    }

    public function removeTier() {
        $tierId = $_POST['form'][0]['value'];
        
        $sqlString = sprintf(
            "DELETE 
               FROM %s
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $tierId
            );
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

    public function editDungeonHtml($dungeonDetails) {
        $raidSize    = array(10, 20);
        $dungeonType = array(0 => 'Standard Dungeon', 1 => 'Special Dungeon (Unranked)');
        $launchDate  = explode(' ', $dungeonDetails->_dateLaunch);

        $html = '';
        $html .= '<form class="admin-form dungeon edit details" id="form-dungeon-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-dungeon-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td><input hidden type="text" name="text-dungeon-id" value="' . $dungeonDetails->_dungeonId . '"/></td></tr>';
        $html .= '<tr><th>Dungeon</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-dungeon" value="' . $dungeonDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Abbreviation</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-abbreviation" value="' . $dungeonDetails->_abbreviation . '"/></td></tr>';
        $html .= '<tr><th>Tier</th></tr>';
        $html .= '<tr><td><select class="admin-select tier" name="select-tier">';
        $html .= '<option value="">Select Tier</option>';
            foreach( CommonDataContainer::$tierArray as $tierId => $tierDetails ):
                if ( $tierId == $dungeonDetails->_tier ):
                    $html .= '<option value="' . $tierId . '" selected>' . $tierId . ' - ' . $tierDetails->_name . '</option>';
                else:
                    $html .= '<option value="' . $tierId . '">' . $tierId . ' - ' . $tierDetails->_name . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Raid Size</th></tr>';
        $html .= '<tr><td><select class="admin-select players" name="select-players">';
            foreach ($raidSize as $players):
                if ( $players == $dungeonDetails->_raidSize ):
                    $html .= '<option value="' . $players . '" selected>' . $players . '-Man</option>';
                else:
                    $html .= '<option value="' . $players . '">' . $players . '-Man</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Launch Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="select-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == date('m', strtotime($dungeonDetails->_dateLaunch)) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="select-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $launchDate[1] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="select-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $launchDate[2] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Dungeon Type</th></tr>';
        $html .= '<tr><td><select class="admin-select dungeon" name="select-dungeon">';
            foreach ($dungeonType as $type => $typeValue):
                if ( $type == $dungeonDetails->_type ):
                    $html .= '<option value="' . $type . '" selected>' . $type . ' - ' . $typeValue . '</option>';
                else:
                    $html .= '<option value="' . $type . '">' . $type . ' - ' . $typeValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>EU Time Difference</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-eu-diff" value="' . $dungeonDetails->_euTimeDiff . '"/></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-dungeon-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }

    public function editDungeon($dungeonId) {
        $html           = '';
        $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

        $html = $this->editDungeonHtml($dungeonDetails);

        echo $html;
        die;
    }

    public function editDungeonDetails() {
        $dungeonId    = $_POST['form'][0]['value'];
        $dungeon      = $_POST['form'][1]['value'];
        $abbreviation = $_POST['form'][2]['value'];
        $tier         = $_POST['form'][3]['value'];
        $raidSize     = $_POST['form'][4]['value'];
        $launchDate   = $_POST['form'][7]['value'] . '-' . $_POST['form'][5]['value'] . '-' .$_POST['form'][6]['value'];
        $dungeonType  = $_POST['form'][8]['value'];
        $euTimeDiff   = $_POST['form'][9]['value'];

        $sqlString = sprintf(
            "UPDATE %s
            SET name = '%s', abbreviation = '%s', tier = '%s', players = '%s', date_launch = '%s', dungeon_type = '%s', eu_diff = '%s'
            WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $dungeon,
            $abbreviation,
            $tier,
            $raidSize,
            $launchDate,
            $dungeonType,
            $euTimeDiff,
            $dungeonId
            );
        die;
    }

    public function removeDungeon() {
        $dungeonId = $_POST['form'][0]['value'];

        $sqlString = sprintf(
            "DELETE 
               FROM %s
              WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $dungeonId
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

    public function editEncounterHtml($encounterDetails) {
        $launchDate = explode('-', $encounterDetails->_dateLaunch);

        $html = '';
        $html .= '<form class="admin-form encounter edit details" id="form-encounter-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-encounter-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td><input hidden type="text" name="text-encounter-id" value="' . $encounterDetails->_encounterId . '"/></td></tr>';
        $html .= '<tr><th>Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-encounter" value="' . $encounterDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Dungeon</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-dungeon" value="' . $encounterDetails->_dungeon . '"/></td></tr>';
        $html .= '<tr><th>Encounter Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-encounter" value="' . $encounterDetails->_encounterName . '"/></td></tr>';
        $html .= '<tr><th>Encounter Short Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-encouter-short" value="' . $encounterDetails->_encounterShortName . '"/></td></tr>';
        $html .= '<tr><th>Launcg Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="select-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == $launchDate[1] ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="select-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $launchDate[2] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="select-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $launchDate[0] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Mob Order</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-mob-order" value="' . $encounterDetails->_encounterOrder . '"/></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-encounter-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }

    public function editEncounter($encounterId) {
        $html             = '';
        $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

        $html = $this->editEncounterHtml($encounterDetails);

        echo $html;
        die;
    }

    public function editEncounterDetails() {
        $encounterId        = $_POST['form'][0]['value'];
        $encounter          = $_POST['form'][1]['value'];
        $dungeon            = $_POST['form'][2]['value'];
        $encounterName      = $_POST['form'][3]['value'];
        $encounterShortName = $_POST['form'][4]['value'];
        $launchDate         = $_POST['form'][7]['value'] . '-' . $_POST['form'][5]['value'] . '-' .$_POST['form'][6]['value'];
        $encounterOrder     = $_POST['form'][8]['value'];

        $sqlString = sprintf(
            "UPDATE %s
            SET name = '%s', dungeon = '%s', encounter_name = '%s', encounter_short_name = '%s', date_launch = '%s', mob_order = '%s'
            WHERE encounter_id = '%s'",
            DbFactory::TABLE_ENCOUNTERS,
            $encounter,
            $dungeon,
            $encounterName,
            $encounterShortName,
            $launchDate,
            $encounterOrder,
            $encounterId
            );
        die;
    }

    public function removeEncounter() {
        $encounterId = $_POST['form'][0]['value'];

        $sqlString = sprintf(
            "DELETE 
               FROM %s
              WHERE encounter_id = '%s'",
            DbFactory::TABLE_ENCOUNTERS,
            $encounterId
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
        $html .= '<form class="admin-form guild edit details" id="form-guild-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
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
        $html .= '<div class="vertical-separator"></div>';
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

    public function getNewsArticle() {
        $dbh         = DbFactory::getDbh();
        $returnArray = array();

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
               ORDER BY date_added DESC", 
                    DbFactory::TABLE_NEWS
                ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $newsId               = $row['news_id'];
            $row['date_added']    = Functions::formatDate($row['date_added'], 'm-d-Y H:i');
            $article              = new Article($row);
            $returnArray[$newsId] = $article;
        }

        return $returnArray;
    }
}

?>