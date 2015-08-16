<?php 

class AdministratorModel extends Model {
    protected $_userDetails;

    const PAGE_TITLE = 'Administrator Control Panel';

    public function __construct($module, $params) {
        parent::__construct($module);

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
                case 'guild-edit':
                    $this->getEditGuild($_POST['guild']);
                //case "guild-edit":
                    //$this->editGuild();
                    //break;
                //case "guild-edit-details":
                    //$this->editGuildDetails();
                    //break;
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

    public function getEditGuildHtml($guildDetails) {
        $html = '';
        $html .= '<form class="admin-form guild edit details" id="form-guild-edit-details" method="POST" action="http://localhost/stage/administrator">';
        $html .= '<table class="admin-guild-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><th>Date Created</th></tr>';
        $html .= '<tr><td>' . $guildDetails->_dateCreated . '</td></tr>';
        $html .= '<tr><th>Leader</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-leader" value="' . $guildDetails->_leader . '"/></td></tr>';
        $html .= '<tr><th>Website</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-website" value="' . $guildDetails->_website . '"/></td></tr>';
        $html .= '<tr><th>Facebook</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-facebook" value="' . $guildDetails->_facebook . '"/></td></tr>';
        $html .= '<tr><th>Twitter</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-twitter" value="' . $guildDetails->_twitter . '"/></td></tr>';
        $html .= '<tr><th>Google</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-google" value="' . $guildDetails->_google . '"/></td></tr>';
        $html .= '<tr><th>Faction</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-faction" value="' . $guildDetails->_faction . '"/></td></tr>';
        $html .= '<tr><th>Server</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-server" value="' . $guildDetails->_server . '"/></td></tr>';
        $html .= '<tr><th>Active</th></tr>';
        $html .= '<tr><td><input type="text" name="text-guild-active" value="' . $guildDetails->_active . '"/></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<input id="admin-submit-guild-edit" type="submit" value="Submit" />';
        $html .= '</form>';

        return $html;
    }

    public function getEditGuild($guildId) {
        $html         = '';
        $guildDetails = CommonDataContainer::$guildArray[$guildId];

        $html = $this->getEditGuildHtml($guildDetails);

        echo $html;
        die;
    }

    public function editGuildDetails() {

    }
}

?>