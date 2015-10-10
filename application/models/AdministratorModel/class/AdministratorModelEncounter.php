<?php

/**
 * encounter administration
 */
class AdministratorModelEncounter {
    protected $_action;
    protected $_dbh;
    protected $_formFields;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('adminpanel-encounter') || Post::get('submit') ) {
            $this->populateFormFields();

            switch ($this->_action) {
                case "add":
                    $this->addNewEncounter();
                    break;
                case "edit":
                    $this->editEncounter(Post::get('adminpanel-encounter'));
                    break;
                case "remove":
                    $this->removeEncounter();
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
        $this->_formFields = new AdminEncounterFormFields();

        $this->_formFields->encounterId        = Post::get('adminpanel-encounter-id');
        $this->_formFields->encounter          = Post::get('adminpanel-encounter');
        $this->_formFields->dungeonId          = Post::get('adminpanel-encounter-dungeon-id');
        $this->_formFields->encounterName      = Post::get('adminpanel-encounter-display-name');
        $this->_formFields->encounterShortName = Post::get('adminpanel-encounter-short-name');
        $this->_formFields->launchDate         = Post::get('adminpanel-encounter-launch-year') . '-' . Post::get('adminpanel-encounter-launch-month') . '-' . Post::get('adminpanel-encounter-launch-day');
        $this->_formFields->encounterOrder     = Post::get('adminpanel-encounter-mob-order');
    }

    /**
     * insert new encounter details into the database
     *
     * @return void
     */
    public function addNewEncounter() {
        $dungeonDetails           = CommonDataContainer::$dungeonArray[$this->_formFields->dungeonId];
        $newDungeonEncounterCount = $dungeonDetails->_numOfEncounters + 1;

        $tierDetails              = CommonDataContainer::$tierArray[$dungeonDetails->_tier];
        $newTierEncounterCount    = $tierDetails->_numOfEncounters + 1;

        // create the encounter sql
        $createEncounterquery=$this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (name, dungeon, dungeon_id, tier, encounter_short_name, date_launch)
            values('%s', '%s', '%s', '%s', '%s', '%s')",
            DbFactory::TABLE_ENCOUNTERS,
            $this->_formFields->encounter,
            $dungeonDetails->_name,
            $dungeonDetails->_dungeonId,
            $dungeonDetails->_tier,
            $this->_formFields->encounterShortName,
            $this->_formFields->launchDate
        ));
        $createEncounterquery->execute();

        // update dungeon encounter count sql
        $updateDungeonQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
                SET mobs = '%s'
              WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $newDungeonEncounterCount,
            $dungeonDetails->_dungeonId
        ));
        $updateDungeonQuery->execute();

        // update tier encounter count sql
        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
                SET encounters = '%s'
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newTierEncounterCount,
            $tierDetails->_tierId
        ));
        $updateTierQuery->execute();
    }

    /**
     * create html to prepare form and display all necessary encounter details
     * 
     * @param  EncounterDetails $encounterDetails [ encounter details object ]
     * 
     * @return string [ return html containing specified dungeon details ]
     */
    public function editEncounterHtml($encounterDetails) {
        $launchDate = explode('-', $encounterDetails->_dateLaunch);

        $html = '';
        $html .= '<form class="admin-form encounter edit details" id="form-encounter-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-encounter-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><th>Encounter Name</th></tr>';
        $html .= '<tr><td><input hidden type="text" name="adminpanel-encounter-id" value="' . $encounterDetails->_encounterId . '"/><input class="admin-textbox" type="text" name="adminpanel-encounter" value="' . $encounterDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Display Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-encounter-display-name" value="' . $encounterDetails->_encounterName . '"/></td></tr>';
        $html .= '<tr><th>Encounter Short Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-encounter-short-name" value="' . $encounterDetails->_encounterShortName . '"/></td></tr>';
        $html .= '<tr><th>Mob Order</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-encounter-mob-order" value="' . $encounterDetails->_encounterOrder . '"/></td></tr>';
        $html .= '<tr><th>Launch Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="adminpanel-encounter-launch-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == $launchDate[1] ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="adminpanel-encounter-launch-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $launchDate[2] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="adminpanel-encounter-launch-year">';
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
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
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
        // if the submit field is present, update encounter data
        if ( Post::get('submit') ) {
            $query = $this->_dbh->prepare(sprintf(
                "UPDATE %s
                    SET name = '%s', 
                        encounter_name = '%s', 
                        encounter_short_name = '%s', 
                        date_launch = '%s', 
                        mob_order = '%s'
                  WHERE encounter_id = '%s'",
                DbFactory::TABLE_ENCOUNTERS,
                $this->_formFields->encounter,
                $this->_formFields->encounterName,
                $this->_formFields->encounterShortName,
                $this->_formFields->launchDate,
                $this->_formFields->encounterOrder,
                $this->_formFields->encounterId
            ));
            $query->execute();
        } else {
            $html             = '';
            $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];

            $html = $this->editEncounterHtml($encounterDetails);

            echo $html;
        }
    }

    /**
     * delete from encounterlist_table by specified id
     * 
     * @return void
     */
    public function removeEncounter() {
        $encounterDetails         = CommonDataContainer::$encounterArray[$this->_formFields->encounter];
        $dungeonDetails           = CommonDataContainer::$dungeonArray[$encounterDetails->_dungeonId];
        $newDungeonEncounterCount = $dungeonDetails->_numOfEncounters - 1;

        $tierDetails           = CommonDataContainer::$tierArray[$dungeonDetails->_tier];
        $newTierEncounterCount = $tierDetails->_numOfEncounters - 1;

        $deleteEncounterQuery = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE encounter_id = '%s'",
            DbFactory::TABLE_ENCOUNTERS,
            $this->_formFields->encounter
        ));
        $deleteEncounterQuery->execute();

        $updateDungeonQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
                SET mobs = '%s'
              WHERE dungeon_id = '%s'",
            DbFactory::TABLE_DUNGEONS,
            $newDungeonEncounterCount,
            $dungeonDetails->_dungeonId
        ));
        $updateDungeonQuery->execute();

        $updateTierQuery = $this->_dbh->prepare(sprintf(
            "UPDATE %s
                SET encounters = '%s'
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $newTierEncounterCount,
            $tierDetails->_tierId
        ));
        $updateTierQuery->execute();
    }
}