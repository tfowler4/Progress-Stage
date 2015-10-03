<?php 
/**
 * class to handle insert, update, and delete of tiers, dungeones,
 * encounters, guild, and news articles
 */
class AdministratorModel extends Model {
    protected $_userDetails;
    protected $_formFields;

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
                Functions::sendTo404();
            }
        } else {
            Functions::sendTo404();
        }

        if ( !empty(Post::get('request')) ) {
            $subModuleParameters = explode('-', Post::get('request'));

            if ( !empty($subModuleParameters) ) {
                $subModuleName   = $subModuleParameters[0];
                $subModuleAction = $subModuleParameters[1];

                switch ($subModuleName) {
                    case 'tier':
                        $subModule = new AdministratorModelTier($subModuleAction, $this->_dbh);
                        break;
                    case 'dungeon':
                        $subModule = new AdministratorModelDungeon($subModuleAction, $this->_dbh);
                        break;
                    case 'encounter':
                        $subModule = new AdministratorModelEncounter($subModuleAction, $this->_dbh);
                        break;
                    case 'guild':
                        $subModule = new AdministratorModelGuild($subModuleAction, $this->_dbh);
                        break;
                    case 'kill':
                        $subModule = new AdministratorModelKill($subModuleAction, $this->_dbh);
                        break;
                    case 'article':
                        $subModule = new AdministratorModelArticle($subModuleAction, $this->_dbh);
                        break;
                    case 'utility':
                        $subModule = new AdministratorModelUtility($subModuleAction, $this->_dbh);
                        break;
                }
            }
        }
    }
}

?>