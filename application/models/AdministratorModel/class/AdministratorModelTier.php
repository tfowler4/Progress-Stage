<?php

/**
 * tier administration
 */
class AdministratorModelTier {
    protected $_action;
    protected $_dbh;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('tier') || Post::get('submit') ) {
            switch ($this->_action) {
                case "add":
                    $this->addNewTier();
                    break;
                case "edit":
                    $this->editTier(Post::get('tier'));
                    break;
                case "remove":
                    $this->removeTier();
                    break;
            }
        } else {
            die;
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
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
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
        // if the submit field is present, update tier data
        if ( Post::get('submit') ) {
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
        } else {
            $html        = '';
            $tierDetails = CommonDataContainer::$tierArray[$tierId];

            $html = $this->editTierHtml($tierDetails);

            echo $html;
        }

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
}