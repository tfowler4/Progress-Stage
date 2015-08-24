<?php 
/**
 * class to handle insert, update, and delete of tiers, dungeones,
 * encounters, guild, and news articles
 */
class AdministratorModel extends Model {
    protected $_userDetails;
    protected $_newsArticleArray = array();
    protected $_dbh;

    const PAGE_TITLE = 'Administrator Control Panel';
    /**
     * constructor
     */
    public function __construct($module, $params) {
        parent::__construct();

        $this->title = self::PAGE_TITLE;

        $this->_dbh = DbFactory::getDbh();

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
                case "article-add":
                    $this->addNewArticle();
                    break;
                case "article-edit":
                    $this->editArticle($_POST['article']);
                    break;
                case "article-edit-details":
                    $this->editArticleDetails();
                    break;
                case "article-remove":
                    $this->removeArticle();
                    break;
            }
        }
    }
    /**
     * insert new tier details into the database
     *
     * @return void
     */
    public function addNewTier() {
        $tier      = Post::get('create-tier-number');
        $altTier   = Post::get('create-tier-alt-number');
        $tierName  = Post::get('create-tier-name');
        $altName   = Post::get('create-tier-alt-name');
        $startDate = Post::get('create-tier-year') . '-' . Post::get('create-tier-month') . '-' . Post::get('create-tier-day');

        $query = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (tier, title, alt_tier, alt_title, date_start)
            values('%s', '%s', '%s', '%s', '%s')",
            DbFactory::TABLE_TIERS,
            $tier,
            $tierName,
            $altTier,
            $altName,
            $startDate
            ));
        $query->execute();

        die;
    }
    /**
     * create html to prepare form and display all necessary tier details
     * 
     * @param  TierDetails $tierDetails [ tier details object ]
     * 
     * @return string                   [ return html containing specified tier details ]
     */
    public function editTierHtml($tierDetails) {
        $startDate = explode(' ', $tierDetails->_dateStart);
        $endDate   = explode(' ', $tierDetails->_dateEnd);

        $html = '';
        $html .= '<form class="admin-form tier edit details" id="form-tier-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-tier-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td><input hidden type="text" name="edit-tier-id" value="' . $tierDetails->_tierId . '"/></td></tr>';
        $html .= '<tr><th>Tier Number</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-tier-number" value="' . $tierDetails->_tier . '"/></td></tr>';
        $html .= '<tr><th>Alt Tier Number</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-tier-alt-number" value="' . $tierDetails->_altTier . '"/></td></tr>';
        $html .= '<tr><th>Tier Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-tier-name" value="' . $tierDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Alt Tier Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-tier-alt-name" value="' . $tierDetails->_altTitle . '"/></td></tr>';
        $html .= '<tr><th>Start Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="edit-tier-start-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == date('m', strtotime($tierDetails->_dateStart)) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="edit-tier-start-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $startDate[1] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="edit-tier-start-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $startDate[2] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>End Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="edit-tier-end-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( ($tierDetails->_dateEnd != 'Currently Active') && ($month == date('m', strtotime($tierDetails->_dateEnd))) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="edit-tier-end-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( ($tierDetails->_dateEnd != 'Currently Active') && ($day == $endDate[1]) ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="edit-tier-end-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( ($tierDetails->_dateEnd != 'Currently Active') && ($year == $endDate[2]) ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }
    /**
     * get id from drop down selection to obtain the specific tier details
     * and pass that array to editTierHtml to display
     * 
     * @param  string $tierId [ id of a specific tier]
     * 
     * @return void
     */
    public function editTier($tierId) {
        $html        = '';
        $tierDetails = CommonDataContainer::$tierArray[$tierId];

        $html = $this->editTierHtml($tierDetails);

        echo $html;
        die;
    }
    /**
     * update tier_table with provided tier details
     * 
     * @return void
     */
    public function editTierDetails() {
        $tierId    = Post::get('edit-tier-id');
        $tier      = Post::get('edit-tier-number');
        $altTier   = Post::get('edit-tier-alt-number');
        $startDate = Post::get('edit-tier-start-year') . '-' . Post::get('edit-tier-start-month') . '-' . Post::get('edit-tier-start-day');
        $endDate   = Post::get('edit-tier-end-year') . '-' . Post::get('edit-tier-end-month') . '-' . Post::get('edit-tier-end-day');
        $tierName  = Post::get('edit-tier-name');
        $altName   = Post::get('edit-tier-alt-name');

        // If date is 2011-01-01, set to 0000-00-00
        if ( $endDate == '2011-01-01' ) {
            $endDate = '0000-00-00';
        }

        $query = $this->_dbh->prepare(sprintf(
            "UPDATE %s
                SET tier = '%s', 
                    alt_tier = '%s', 
                    date_start = '%s', 
                    date_end = '%s', 
                    title = '%s', 
                    alt_title = '%s'
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $tier,
            $altTier,
            $startDate,
            $endDate,
            $tierName,
            $altName,
            $tierId
            ));
        $query->execute();
        die;
    }
    /**
     * delete from tier_table by specified id
     * 
     * @return void
     */
    public function removeTier() {
        $tierId = Post::get('remove-tier-id');

        $query = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $tierId
            ));
        $query->execute();
        die;
    }
    /**
     * insert new dungeon details into the database
     *
     * @return void
     */
    public function addNewDungeon() {
        $dungeon   = Post::get('create-dungeon-name');
        $tier      = Post::get('create-dungeon-tier-name');
        $numOfMobs = Post::get('create-dungeon-number-of-mobs');

        $tierDetails     = CommonDataContainer::$tierArray[$tier];
        $tierId          = $tierDetails->_tierId;
        $newDungeonCount = $tierDetails->_numOfDungeons + 1;

        $createDungeonQuery = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (name, tier, mobs)
            values('%s', '%s', '%s')",
            DbFactory::TABLE_DUNGEONS,
            $dungeon,
            $tier,
            $numOfMobs
            ));
        $createDungeonQuery->execute();

        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET dungeons = '%s'
            WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newDungeonCount,
            $tierId
        ));
        $updateTierQuery->execute();
        die;
    }
    /**
     * create html to prepare form and display all necessary dungeon details
     * 
     * @param  DungeonDetails $dungeonDetails [ dungeon details object ]
     * 
     * @return string                         [ return html containing specified dungeon details ]
     */
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
        $html .= '<tr><td><input hidden type="text" name="edit-dungeon-id" value="' . $dungeonDetails->_dungeonId . '"/></td></tr>';
        $html .= '<tr><th>Dungeon Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-dungeon-name" value="' . $dungeonDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Abbreviation</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-dungeon-abbreviation" value="' . $dungeonDetails->_abbreviation . '"/></td></tr>';
        $html .= '<tr><th>Tier</th></tr>';
        $html .= '<tr><td><select class="admin-select tier" name="edit-dungeon-tier-number">';
        $html .= '<option value="">Select Tier</option>';
            foreach( CommonDataContainer::$tierArray as $tier => $tierDetails ):
                if ( $tier == $dungeonDetails->_tier ):
                    $html .= '<option value="' . $tierDetails->_tier . '" selected>' . $tierDetails->_tier . ' - ' . $tierDetails->_name . '</option>';
                else:
                    $html .= '<option value="' . $tierDetails->_tier . '">' . $tierDetails->_tier . ' - ' . $tierDetails->_name . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Raid Size</th></tr>';
        $html .= '<tr><td><select class="admin-select players" name="edit-dungeon-players">';
            foreach ($raidSize as $players):
                if ( $players == $dungeonDetails->_raidSize ):
                    $html .= '<option value="' . $players . '" selected>' . $players . '-Man</option>';
                else:
                    $html .= '<option value="' . $players . '">' . $players . '-Man</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Dungeon Type</th></tr>';
        $html .= '<tr><td><select class="admin-select dungeon" name="edit-dungeon-type">';
            foreach ($dungeonType as $type => $typeValue):
                if ( $type == $dungeonDetails->_type ):
                    $html .= '<option value="' . $type . '" selected>' . $type . ' - ' . $typeValue . '</option>';
                else:
                    $html .= '<option value="' . $type . '">' . $type . ' - ' . $typeValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>EU Time Difference</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-dungeon-eu-diff" value="' . $dungeonDetails->_euTimeDiff . '"/></td></tr>';
        $html .= '<tr><th>Launch Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="edit-dungeon-launch-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == date('m', strtotime($dungeonDetails->_dateLaunch)) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="edit-dungeon-launch-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $launchDate[1] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="edit-dungeon-launch-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $launchDate[2] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-dungeon-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }
    /**
     * get id from drop down selection to obtain the specific dungeon details
     * and pass that array to editDungeonHtml to display
     * 
     * @param  string $dungeonId [ id of a specific dungeon ]
     * 
     * @return void
     */
    public function editDungeon($dungeonId) {
        $html           = '';
        $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

        $html = $this->editDungeonHtml($dungeonDetails);

        echo $html;
        die;
    }
    /**
     * update dungeon_table with provided dungeon details
     * 
     * @return void
     */
    public function editDungeonDetails() {
        $dungeonId    = POST::get('edit-dungeon-id');
        $dungeon      = POST::get('edit-dungeon-name');
        $abbreviation = POST::get('edit-dungeon-abbreviation');
        $tier         = POST::get('edit-dungeon-tier-number');
        $raidSize     = POST::get('edit-dungeon-players');
        $launchDate   = POST::get('edit-dungeon-launch-year') . '-' . POST::get('edit-dungeon-launch-month') . '-' . POST::get('edit-dungeon-launch-day');
        $dungeonType  = POST::get('edit-dungeon-type');
        $euTimeDiff   = POST::get('edit-dungeon-eu-diff');

        $query = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET name = '%s', 
                abbreviation = '%s', 
                tier = '%s', 
                players = '%s', 
                date_launch = '%s', 
                dungeon_type = '%s', 
                eu_diff = '%s'
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
            ));
        $query->execute();
        die;
    }
    /**
     * delete from dungeon_table by specified id
     * 
     * @return void
     */
    public function removeDungeon() {
        $dungeonId = POST::get('remove-dungeon-id');

        $dungeonDetails  = CommonDataContainer::$dungeonArray[$dungeonId];
        $tierDetails     = CommonDataContainer::$tierArray[$dungeonDetails->_tier];
        $tierId          = $tierDetails->_tierId;
        $newDungeonCount = $tierDetails->_numOfDungeons - 1;

        $deleteDungeonQuery = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $dungeonId
            ));
        $deleteDungeonQuery->execute();

        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET dungeons = '%s'
            WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newDungeonCount,
            $tierId
        ));
        $updateTierQuery->execute();
        die;
    }
    /**
     * insert new encounter details into the database
     *
     * @return void
     */
    public function addNewEncounter() {
        $encounter  = POST::get('create-encounter-name');
        $dungeonId  = POST::get('create-encounter-dungeon-id');
        $tierNumber = POST::get('create-encounter-tier-number');

        $dungeonDetails           = CommonDataContainer::$dungeonArray[$dungeonId];
        $dungeonName              = $dungeonDetails->_name;
        $newDungeonEncounterCount = $dungeonDetails->_numOfEncounters + 1;

        $tierDetails              = CommonDataContainer::$tierArray[$tierNumber];
        $tierId                   = $tierDetails->_tierId;
        $newTierEncounterCount    = $tierDetails->_numOfEncounters + 1;

        $createEncounterquery=$this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (name, dungeon, dungeon_id, tier)
            values('%s', '%s', '%s', '%s')",
            DbFactory::TABLE_ENCOUNTERS,
            $encounter,
            $dungeonName,
            $dungeonId,
            $tierNumber
            ));
        $createEncounterquery->execute();

        $updateDungeonQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET mobs = '%s'
            WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $newDungeonEncounterCount,
            $dungeonId
        ));
        $updateDungeonQuery->execute();

        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET encounters = '%s'
            WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newTierEncounterCount,
            $tierId
        ));
        $updateTierQuery->execute();
        die;
    }
    /**
     * create html to prepare form and display all necessary encounter details
     * 
     * @param  EncounterDetails $encounterDetails [ encounter details object ]
     * 
     * @return string                             [ return html containing specified dungeon details ]
     */
    public function editEncounterHtml($encounterDetails) {
        $launchDate = explode('-', $encounterDetails->_dateLaunch);

        $html = '';
        $html .= '<form class="admin-form encounter edit details" id="form-encounter-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-encounter-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td><input hidden type="text" name="edit-encounter-id" value="' . $encounterDetails->_encounterId . '"/></td></tr>';
        $html .= '<tr><th>Encounter Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-encounter-name" value="' . $encounterDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Dungeon Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-encounter-dungeon-name" value="' . $encounterDetails->_dungeon . '"/></td></tr>';
        $html .= '<tr><th>Display Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-encounter-display-name" value="' . $encounterDetails->_encounterName . '"/></td></tr>';
        $html .= '<tr><th>Encounter Short Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-encounter-short-name" value="' . $encounterDetails->_encounterShortName . '"/></td></tr>';
        $html .= '<tr><th>Mob Order</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-encounter-mob-order" value="' . $encounterDetails->_encounterOrder . '"/></td></tr>';
        $html .= '<tr><th>Launch Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="edit-encounter-launch-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == $launchDate[1] ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="edit-encounter-launch-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $launchDate[2] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="edit-encounter-launch-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $launchDate[0] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-encounter-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }
    /**
     * get id from drop down selection to obtain the specific encounter details
     * and pass that array to editEncounterHtml to display
     * 
     * @param  string $encounterId [ id of a specific encounter ]
     * 
     * @return void
     */
    public function editEncounter($encounterId) {
        $html             = '';
        $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

        $html = $this->editEncounterHtml($encounterDetails);

        echo $html;
        die;
    }
    /**
     * update encounterlist_table with provided encounter details
     * 
     * @return void
     */
    public function editEncounterDetails() {
        $encounterId        = POST::get('edit-encounter-id');
        $encounter          = POST::get('edit-encounter-name');
        $dungeon            = POST::get('edit-encounter-dungeon-name');
        $encounterName      = POST::get('edit-encounter-display-name');
        $encounterShortName = POST::get('edit-encounter-short-name');
        $launchDate         = POST::get('edit-encounter-launch-year') . '-' . POST::get('edit-encounter-launch-month') . '-' . POST::get('edit-encounter-launch-day');
        $encounterOrder     = POST::get('edit-encounter-mob-order');

        $query = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET name = '%s', 
                dungeon = '%s', 
                encounter_name = '%s', 
                encounter_short_name = '%s', 
                date_launch = '%s', 
                mob_order = '%s'
            WHERE encounter_id = '%s'",
            DbFactory::TABLE_ENCOUNTERS,
            $encounter,
            $dungeon,
            $encounterName,
            $encounterShortName,
            $launchDate,
            $encounterOrder,
            $encounterId
            ));
        $query->execute();
        die;
    }
    /**
     * delete from encounterlist_table by specified id
     * 
     * @return void
     */
    public function removeEncounter() {
        $encounterId = POST::get('remove-encounter-id');

        $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

        $dungeonDetails           = CommonDataContainer::$dungeonArray[$encounterDetails->_dungeonId];
        $dungeonId                = $dungeonDetails->_dungeonId;
        $newDungeonEncounterCount = $dungeonDetails->_numOfEncounters - 1;

        $tierDetails           = CommonDataContainer::$tierArray[$dungeonDetails->_tier];
        $tierId                = $tierDetails->_tierId;
        $newTierEncounterCount = $tierDetails->_numOfEncounters - 1;

        $deleteEncounterQuery = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE encounter_id = '%s'",
            DbFactory::TABLE_ENCOUNTERS,
            $encounterId
            ));
        $deleteEncounterQuery->execute();

        $updateDungeonQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET mobs = '%s'
            WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $newDungeonEncounterCount,
            $dungeonId
        ));
        $updateDungeonQuery->execute();

        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
            SET encounters = '%s'
            WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newTierEncounterCount,
            $tierId
        ));
        $updateTierQuery->execute();
        die;
    }
    /**
     * insert new guild details into the database
     *
     * @return void
     */
    public function addNewGuild() {
        $guild   = POST::get('create-guild-name');
        $server  = POST::get('create-guild-server');
        $country = POST::get('create-guild-country');

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
        $html         = '';
        $guildDetails = CommonDataContainer::$guildArray[$guildId];

        $html = $this->editGuildHtml($guildDetails);
        
        echo $html;
        die;
    }
    /**
     * update guild_table with provided guild details
     * 
     * @return void
     */
    public function editGuildDetails() {
        $guildId  = POST::get('edit-guild-id');
        $leader   = POST::get('edit-guild-leader');
        $website  = POST::get('edit-guild-website');
        $facebook = POST::get('edit-guild-facebook');
        $twitter  = POST::get('edit-guild-twitter');
        $google   = POST::get('edit-guild-google');
        $faction  = POST::get('edit-guild-faction');
        $server   = POST::get('edit-guild-server');
        $active   = POST::get('edit-guild-active');

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
        die;
    }
    /**
     * delete from guild_table by specified id
     * 
     * @return void
     */
    public function removeGuild() {
        $guildId = POST::get('remove-guild-id');

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
    /**
     * get news article details from database
     * 
     * @return array [ return an array of article details object ]
     */
    public function getNewsArticle() {
        $returnArray = array();

        $query = $this->_dbh->prepare(sprintf(
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
    /**
     * insert new article details into the database
     *
     * @return void
     */
    public function addNewArticle() {
        $title   = $_POST['form'][0]['value'];
        $author  = $_POST['form'][1]['value'];
        $content = $_POST['form'][2]['value'];

        $sqlString = sprintf(
            "INSERT INTO %s
            (title, content, added_by)
            values('%s', '%s', '%s')",
            DbFactory::TABLE_NEWS,
            $title,
            $content,
            $author
            );
        die;
    }
    /**
     * create html to prepare form and display all necessary news article details
     * 
     * @param  Article $newsArticle [ article object ]
     *
     * @param  string $articleId    [ id of specific article]
     * 
     * @return string               [ return html containing specified dungeon details ]
     */
    public function editArticleHtml($newsArticle, $articleId) {
        $html = '';
        $html .= '<form class="admin-form news edit" id="form-article-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-article-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><td><input hidden type="text" name="text-article-id" value="' . $articleId . '"/></td></tr>';
        $html .= '<tr><th>Article Title</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-article-title" value="' . $newsArticle->title . '"/></td></tr>';
        $html .= '<tr><th>Date</th></tr>';
        $html .= '<tr><td>' . $newsArticle->date . '</td></tr>';
        $html .= '<tr><th>Author</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="text-author" value="' . $newsArticle->postedBy . '"/></td></tr>';
        $html .= '<tr><th>Content</th></tr>';
        $html .= '<tr><td><textarea class="admin-textarea" name="textarea-content" style="height:225px; text-align:left;"">' . $newsArticle->content . '</textarea></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-article-edit" type="submit" value="Submit" />';
        $html .= '</form>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<form class="admin-form news remove" id="form-article-remove" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<input hidden type="text" name="text-article-id" value="' . $articleId . '"/>';
        $html .= '<input id="admin-submit-article-remove" type="submit" value="Remove" />';
        $html .= '</form>';

        return $html;
    }
    /**
     * get id from drop down selection to obtain the specific article details
     * and pass that array to editArticleHtml to display
     * 
     * @param  string $articleId [ id of a specific news article ]
     * 
     * @return void
     */
    public function editArticle($articleId) {
        $html = '';

        $this->_newsArticleArray = $this->getNewsArticle();
        $newsArticle = $this->_newsArticleArray[$articleId];

        $html = $this->editArticleHtml($newsArticle, $articleId);
        echo $html;
        die;
    }
    /**
     * update news_table with provided article details
     * 
     * @return void
     */
    public function editArticleDetails() {
        $articleId = $_POST['form'][0]['value'];
        $title     = $_POST['form'][1]['value'];
        $author    = $_POST['form'][2]['value'];
        $content   = $_POST['form'][3]['value'];

        $sqlString = sprintf(
            "UPDATE %s
            SET title = '%s', content = '%s',  added_by = '%s'
            WHERE news_id = '%s'",
            DbFactory::TABLE_NEWS,
            $title,
            $content,
            $author,
            $articleId
            );
        die;
    }
    /**
     * delete from news_table by specified id
     * 
     * @return void
     */
    public function removeArticle() {
        $articleId = $_POST['form'][0]['value'];

        $sqlString = sprintf(
            "DELETE 
               FROM %s
              WHERE news_id = '%s'",
            DbFactory::TABLE_NEWS,
            $articleId
            );
        die;
    }
}

?>