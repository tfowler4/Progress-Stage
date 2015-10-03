<?php

/**
 * encounter administration
 */
class AdministratorModelEncounter {
    protected $_action;
    protected $_dbh;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('encounter') || Post::get('submit') ) {
            switch ($this->_action) {
                case "add":
                    $this->addNewEncounter();
                    break;
                case "edit":
                    $this->editEncounter(Post::get('encounter'));
                    break;
                case "remove":
                    $this->removeEncounter();
                    break;
            }
        }

        die;
    }

    /**
     * insert new encounter details into the database
     *
     * @return void
     */
    public function addNewEncounter() {
        $encounter  = Post::get('create-encounter-name');
        $dungeonId  = Post::get('create-encounter-dungeon-id');
        $tierNumber = Post::get('create-encounter-tier-number');

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
            $encounterId        = Post::get('edit-encounter-id');
            $encounter          = Post::get('edit-encounter-name');
            $dungeon            = Post::get('edit-encounter-dungeon-name');
            $encounterName      = Post::get('edit-encounter-display-name');
            $encounterShortName = Post::get('edit-encounter-short-name');
            $launchDate         = Post::get('edit-encounter-launch-year') . '-' . Post::get('edit-encounter-launch-month') . '-' . Post::get('edit-encounter-launch-day');
            $encounterOrder     = Post::get('edit-encounter-mob-order');

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
        $encounterId = Post::get('remove-encounter-id');

        $encounterDetails         = CommonDataContainer::$encounterArray[$encounterId];
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
    }
}