<?php

/**
 * article administration
 */
class AdministratorModelArticle {
    protected $_action;
    protected $_dbh;

    /**
     * constructor
     */
    public function __construct($action, $dbh) {
        $this->_action = $action;
        $this->_dbh    = $dbh;

        if ( Post::get('adminpanel-article') || Post::get('submit') ) {
            switch ($this->_action) {
                case "add":
                    $this->addNewArticle();
                    break;
                case "edit":
                    $this->editArticle(Post::get('adminpanel-article'));
                    break;
                case "remove":
                    $this->removeArticle();
                    break;
            }
        }

        die;
    }

    /**
     * insert new article details into the database
     *
     * @return void
     */
    public function addNewArticle() {
        $dateAdded = date('Y-m-d H:i', strtotime('now')) .':00';
        $content   = Post::get('adminpanel-article');
        $title     = Post::get('adminpanel-article-title');
        $author    = Post::get('adminpanel-article-author');

        $query = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (title, content, date_added, added_by)
            values('%s', '%s', '%s', '%s')",
            DbFactory::TABLE_NEWS,
            $title,
            $content,
            $dateAdded,
            $author
            ));
        $query->execute();
    }

    /**
     * create html to prepare form and display all necessary news article details
     * 
     * @param  Article $newsArticle [ article object ]
     * @param  string  $articleId   [ id of specific article]
     * 
     * @return string [ return html containing specified dungeon details ]
     */
    public function editArticleHtml($newsArticle, $articleId) {
        $html = '';
        $html .= '<form class="admin-form news edit" id="form-article-edit-details" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<table class="admin-article-listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= '<tr><th>Article Title</th></tr>';
        $html .= '<tr><td><input hidden type="text" name="adminpanel-article" value="' . $articleId . '"/><input class="admin-textbox" type="text" name="adminpanel-article-title" value="' . $newsArticle->title . '"/></td></tr>';
        $html .= '<tr><th>Date</th></tr>';
        $html .= '<tr><td>' . $newsArticle->date . '</td></tr>';
        $html .= '<tr><th>Author</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="adminpanel-article-author" value="' . $newsArticle->postedBy . '"/></td></tr>';
        $html .= '<tr><th>Content</th></tr>';
        $html .= '<tr><td><textarea id="edit-article" class="admin-textarea" name="adminpanel-article-content" style="height:225px; text-align:left;"">' . $newsArticle->content . '</textarea></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
        $html .= '<input id="admin-submit-article-edit" type="submit" value="Submit" />';
        $html .= '</form>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<form class="admin-form news remove" id="form-article-remove" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<input hidden type="text" name="adminpanel-article" value="' . $articleId . '"/>';
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
        // if the submit field is present, update article data
        if ( Post::get('submit') ) {
            $articleId = Post::get('adminpanel-article');
            $title     = Post::get('adminpanel-article-title');
            $author    = Post::get('adminpanel-article-author');
            $content   = Post::get('adminpanel-article-content');

            $query = $this->_dbh->prepare(sprintf(
                "UPDATE %s
                    SET title = '%s', 
                        content = '%s',  
                        added_by = '%s'
                  WHERE news_id = '%s'",
                DbFactory::TABLE_NEWS,
                $title,
                $content,
                $author,
                $articleId
                ));
            $query->execute();
        } else {
            $html = '';

            $newsArticle = $this->getNewsArticle($articleId);

            $html = $this->editArticleHtml($newsArticle, $articleId);
            echo $html;
        }
    }

    /**
     * delete from news_table by specified id
     * 
     * @return void
     */
    public function removeArticle() {
        $articleId = Post::get('adminpanel-article');

        $query = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE news_id = '%s'",
            DbFactory::TABLE_NEWS,
            $articleId
        ));
        $query->execute();
    }

    /**
     * get specific news article query
     * 
     * @param  string $articleTitle [ title of news article ]
     * 
     * @return Article [ article object ]
     */
    public function getNewsArticle($articleId) {
        $query = $this->_dbh->prepare(sprintf(
            "SELECT news_id,
                    title,
                    content,
                    date_added,
                    added_by,
                    published,
                    type
               FROM %s
              WHERE published = 1
                AND news_id = %d", 
                    DbFactory::TABLE_NEWS, 
                    $articleId
        ));
        $query->execute();

        return new Article($query->fetch());
    }
}