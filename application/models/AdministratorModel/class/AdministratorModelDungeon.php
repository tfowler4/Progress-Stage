<?php

/**
 * dungeon administration
 */
class AdministratorModelDungeon {
    protected $_action;
    protected $_dbh;
    protected $_formFields;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('adminpanel-dungeon') || Post::get('submit') ) {
            $this->populateFormFields();

            switch ($this->_action) {
                case "add":
                    $this->addNewDungeon();
                    break;
                case "edit":
                    $this->editDungeon(Post::get('adminpanel-dungeon'));
                    break;
                case "remove":
                    $this->removeDungeon();
                    break;
            }
        }

        die;
    }

    /**
     * populates form field object with data from form
     * 
     * @return void
     */
    public function populateFormFields() {
        $this->_formFields = new AdminDungeonFormFields();

        $this->_formFields->dungeonId    = Post::get('adminpanel-dungeon-id');
        $this->_formFields->dungeon      = Post::get('adminpanel-dungeon');
        $this->_formFields->abbreviation = Post::get('adminpanel-dungeon-abbreviation');
        $this->_formFields->tier         = Post::get('adminpanel-dungeon-tier');
        $this->_formFields->raidSize     = Post::get('adminpanel-dungeon-players');
        $this->_formFields->launchDate   = Post::get('adminpanel-dungeon-launch-year') . '-' . Post::get('adminpanel-dungeon-launch-month') . '-' . Post::get('adminpanel-dungeon-launch-day');
        $this->_formFields->dungeonType  = Post::get('adminpanel-dungeon-type');
        $this->_formFields->euTimeDiff   = Post::get('adminpanel-dungeon-eu-diff');
    }

    /**
     * insert new dungeon details into the database
     *
     * @return void
     */
    public function addNewDungeon() {
        $tierDetails     = CommonDataContainer::$tierArray[$this->_formFields->tier];
        $newDungeonCount = $tierDetails->_numOfDungeons + 1;

        $createDungeonQuery = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (name, tier, abbreviation, players, date_launch, dungeon_type, eu_diff)
            values('%s', '%s', '%s', '%s', '%s', '%s', '%s')",
            DbFactory::TABLE_DUNGEONS,
            $this->_formFields->dungeon,
            $this->_formFields->tier,
            $this->_formFields->abbreviation,
            $this->_formFields->raidSize,
            $this->_formFields->launchDate,
            $this->_formFields->dungeonType,
            $this->_formFields->euTimeDiff
        ));
        $createDungeonQuery->execute();

        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
                SET dungeons = '%s'
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newDungeonCount,
            $tierDetails->_tierId
        ));
        $updateTierQuery->execute();
    }

    /**
     * create html to prepare form and display all necessary dungeon details
     * 
     * @param  DungeonDetails $dungeonDetails [ dungeon details object ]
     * 
     * @return string [ return html containing specified dungeon details ]
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
        $html .= '<tr><th>Dungeon Name</th></tr>';
        $html .= '<tr><td><input hidden type="text" name="adminpanel-dungeon-id" value="' . $dungeonDetails->_dungeonId . '"/><input class="admin-textbox" type="text" name="adminpanel-dungeon" value="' . $dungeonDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Abbreviation</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-dungeon-abbreviation" value="' . $dungeonDetails->_abbreviation . '"/></td></tr>';
        $html .= '<tr><th>Tier</th></tr>';
        $html .= '<tr><td><select class="admin-select tier" name="adminpanel-dungeon-tier">';
        $html .= '<option value="">Select Tier</option>';
            foreach( CommonDataContainer::$tierArray as $tier => $tierDetails ) {
                if ( $tier == $dungeonDetails->_tier ) {
                    $html .= '<option value="' . $tierDetails->_tier . '" selected>' . $tierDetails->_tier . ' - ' . $tierDetails->_name . '</option>';
                } else {
                    $html .= '<option value="' . $tierDetails->_tier . '">' . $tierDetails->_tier . ' - ' . $tierDetails->_name . '</option>';
                }
            }
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Raid Size</th></tr>';
        $html .= '<tr><td><select class="admin-select players" name="adminpanel-dungeon-players">';
            foreach ($raidSize as $players) {
                if ( $players == $dungeonDetails->_raidSize ) {
                    $html .= '<option value="' . $players . '" selected>' . $players . '-Man</option>';
                } else {
                    $html .= '<option value="' . $players . '">' . $players . '-Man</option>';
                }
            }
        $html .= '</select></td></tr>';
        $html .= '<tr><th>Dungeon Type</th></tr>';
        $html .= '<tr><td><select class="admin-select dungeon" name="adminpanel-dungeon-type">';
            foreach ($dungeonType as $type => $typeValue) {
                if ( $type == $dungeonDetails->_type ) {
                    $html .= '<option value="' . $type . '" selected>' . $type . ' - ' . $typeValue . '</option>';
                } else {
                    $html .= '<option value="' . $type . '">' . $type . ' - ' . $typeValue . '</option>';
                }
            }
        $html .= '</select></td></tr>';
        $html .= '<tr><th>EU Time Difference</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-dungeon-eu-diff" value="' . $dungeonDetails->_euTimeDiff . '"/></td></tr>';
        $html .= '<tr><th>Launch Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="adminpanel-dungeon-launch-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue) {
                if ( $month == date('m', strtotime($dungeonDetails->_dateLaunch)) ) {
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                } else {
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                }
            }
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="adminpanel-dungeon-launch-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue) {
                if ( $day == $launchDate[1] ) {
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                } else {
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                }
            }
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="adminpanel-dungeon-launch-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue) {
                if ( $year == $launchDate[2] ){
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                } else {
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                }
            }
        $html .= '</select></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
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
        // if the submit field is present, update dungeon data
        if ( Post::get('submit') ) {
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
                $this->_formFields->dungeon,
                $this->_formFields->abbreviation,
                $this->_formFields->tier,
                $this->_formFields->raidSize,
                $this->_formFields->launchDate,
                $this->_formFields->dungeonType,
                $this->_formFields->euTimeDiff,
                $this->_formFields->dungeonId
            ));
            $query->execute();
        } else {
            $html           = '';
            $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

            $html = $this->editDungeonHtml($dungeonDetails);

            echo $html;
        }
    }

    /**
     * delete from dungeon_table by specified id
     * 
     * @return void
     */
    public function removeDungeon() {
        $dungeonDetails  = CommonDataContainer::$dungeonArray[$this->_formFields->dungeon];
        $tierDetails     = CommonDataContainer::$tierArray[$dungeonDetails->_tier];
        $newDungeonCount = $tierDetails->_numOfDungeons - 1;

        $deleteDungeonQuery = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $this->_formFields->dungeon
        ));
        $deleteDungeonQuery->execute();

        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
                SET dungeons = '%s'
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newDungeonCount,
            $tierDetails->_tierId
        ));
        $updateTierQuery->execute();
    }
}