<?php

/**
 * tier administration
 */
class AdministratorModelTier {
    protected $_action;
    protected $_dbh;
    protected $_formFields;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('adminpanel-tier') || Post::get('submit') ) {
            $this->populateFormFields();

            switch ($this->_action) {
                case "add":
                    $this->addNewTier();
                    break;
                case "edit":
                    $this->editTier(Post::get('adminpanel-tier'));
                    break;
                case "remove":
                    $this->removeTier();
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
        $this->_formFields = new AdminTierFormFields();

        $this->_formFields->tierId     = Post::get('adminpanel-tier-id');
        $this->_formFields->tierNumber = Post::get('adminpanel-tier');
        $this->_formFields->altTier    = Post::get('adminpanel-tier-alt-number');
        $this->_formFields->tierName   = Post::get('adminpanel-tier-name');
        $this->_formFields->altName    = Post::get('adminpanel-tier-alt-name');
        $this->_formFields->startDate  = Post::get('adminpanel-tier-start-year') . '-' . Post::get('adminpanel-tier-start-month') . '-' . Post::get('adminpanel-tier-start-day');
        $this->_formFields->endDate    = Post::get('adminpanel-tier-end-year') . '-' . Post::get('adminpanel-tier-end-month') . '-' . Post::get('adminpanel-tier-end-day');
    }

    /**
     * insert new tier details into the database
     *
     * @return void
     */
    public function addNewTier() {
        $query = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (tier, title, alt_tier, alt_title, date_start)
            values('%s', '%s', '%s', '%s', '%s')",
            DbFactory::TABLE_TIERS,
            $this->_formFields->tierNumber,
            $this->_formFields->tierName,
            $this->_formFields->altTier,
            $this->_formFields->altName,
            $this->_formFields->startDate
            ));
        $query->execute();
    }

    /**
     * create html to prepare form and display all necessary tier details
     * 
     * @param  TierDetails $tierDetails [ tier details object ]
     * 
     * @return string [ return html containing specified tier details ]
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
        $html .= '<tr><th>Tier Number (Base Number)</th></tr>';
        $html .= '<tr><td><input hidden type="text" name="adminpanel-tier-id" value="' . $tierDetails->_tierId . '"/><input class="admin-textbox" type="text" name="adminpanel-tier" value="' . $tierDetails->_tier . '"/></td></tr>';
        $html .= '<tr><th>Alt Tier Number (Expansion Abbreviation + #)</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-tier-alt-number" value="' . $tierDetails->_altTier . '"/></td></tr>';
        $html .= '<tr><th>Tier Name</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-tier-name" value="' . $tierDetails->_name . '"/></td></tr>';
        $html .= '<tr><th>Alt Tier Name (Shortened Tier Name)</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-tier-alt-name" value="' . $tierDetails->_altTitle . '"/></td></tr>';
        $html .= '<tr><th>Start Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="adminpanel-tier-start-month">';
            foreach( CommonDataContainer::$monthsArray as $month => $monthValue):
                if ( $month == date('m', strtotime($tierDetails->_dateStart)) ):
                    $html .= '<option value="' . $month . '" selected>' . $monthValue . '</option>';
                else:
                    $html .='<option value="' . $month . '">' . $monthValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select day" name="adminpanel-tier-start-day">';
            foreach( CommonDataContainer::$daysArray as $day => $dayValue):
                if ( $day == $startDate[1] ):
                    $html .= '<option value="' . $day . '" selected>' . $dayValue . '</option>';
                else:
                    $html .='<option value="' . $day . '">' . $dayValue . '</option>';
                endif;
            endforeach;
        $html .= '</select>';
        $html .= '<select class="admin-select year" name="adminpanel-tier-start-year">';
            foreach( CommonDataContainer::$yearsArray as $year => $yearValue):
                if ( $year == $startDate[2] ):
                    $html .= '<option value="' . $year . '" selected>' . $yearValue . '</option>';
                else:
                    $html .= '<option value="' . $year . '">' . $yearValue . '</option>';
                endif;
            endforeach;
        $html .= '</select></td></tr>';
        $html .= '<tr><th>End Date</th></tr>';
        $html .= '<tr><td><select class="admin-select month" name="adminpanel-tier-end-month">';
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
            // If date is 2011-01-01, set to 0000-00-00
            if ( $this->_formFields->endDate == '2011-01-01' ) {
                $this->_formFields->endDate = '0000-00-00';
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
                $this->_formFields->tierNumber,
                $this->_formFields->altTier,
                $this->_formFields->startDate,
                $this->_formFields->endDate,
                $this->_formFields->tierName,
                $this->_formFields->altName,
                $this->_formFields->tierId
                ));
            $query->execute();
        } else {
            $html        = '';

            $tierDetails = CommonDataContainer::$tierArray[$tierId];

            $html = $this->editTierHtml($tierDetails);

            echo $html;
        }
    }

    /**
     * delete from tier_table by specified id
     * 
     * @return void
     */
    public function removeTier() {
        $query = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE tier_id = '%s'",
            DbFactory::TABLE_TIERS,
            $this->_formFields->tierNumber
        ));
        $query->execute();
    }
}