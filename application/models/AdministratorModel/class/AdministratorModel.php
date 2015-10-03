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

    /**
     * get news article details from database
     * 
     * @return array [ return an array of article details object ]
     */
    public function getNewsArticle() {
        $returnArray = array();

        $query = $this->_dbh->prepare(sprintf(
            "SELECT news_id,
                    title,
                    content,
                    date_added,
                    added_by,
                    published,
                    type
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
     * get html of sub-model template file
     * 
     * @param  object $data [ page data ]
     * 
     * @return void
     */
    public function getHTML($subModule, $data) {
        $htmlFilePath = ABSOLUTE_PATH . '/application/models/AdministratorModel/html/' . $subModule . '.html';

        if ( file_exists($htmlFilePath) ) {
            include $htmlFilePath;
        } else {
            Functions::sendTo404();
        }
    }
}

?>