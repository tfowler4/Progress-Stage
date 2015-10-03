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

        if ( Post::get('article') || Post::get('submit') ) {
            switch ($this->_action) {
                case "add":
                    $this->addNewArticle();
                    break;
                case "edit":
                    $this->editArticle(Post::get('article'));
                    break;
                case "remove":
                    $this->removeArticle();
                    break;
            }
        } else {
            die;
        }
    }

    /**
     * insert new article details into the database
     *
     * @return void
     */
    public function addNewArticle() {
        $title   = Post::get('create-article-title');
        $author  = Post::get('create-article-author');
        $content = Post::get('create-article-content');

        $query = $this->_dbh->prepare(sprintf(
            "INSERT INTO %s
            (title, content, added_by)
            values('%s', '%s', '%s')",
            DbFactory::TABLE_NEWS,
            $title,
            $content,
            $author
            ));
        $query->execute();
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
        $html .= '<tr><td><input hidden type="text" name="edit-article-id" value="' . $articleId . '"/></td></tr>';
        $html .= '<tr><th>Article Title</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-article-title" value="' . $newsArticle->title . '"/></td></tr>';
        $html .= '<tr><th>Date</th></tr>';
        $html .= '<tr><td>' . $newsArticle->date . '</td></tr>';
        $html .= '<tr><th>Author</th></tr>';
        $html .= '<tr><td><input class="admin-textbox" type="text" name="edit-article-author" value="' . $newsArticle->postedBy . '"/></td></tr>';
        $html .= '<tr><th>Content</th></tr>';
        $html .= '<tr><td><textarea class="admin-textarea" name="edit-article-content" style="height:225px; text-align:left;"">' . $newsArticle->content . '</textarea></td></tr>';
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<input id="admin-submit-tier-edit-action" type="hidden" name="submit" value="submit" />';
        $html .= '<input id="admin-submit-article-edit" type="submit" value="Submit" />';
        $html .= '</form>';
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<form class="admin-form news remove" id="form-article-remove" method="POST" action="' . PAGE_ADMIN . '">';
        $html .= '<input hidden type="text" name="remove-article-id" value="' . $articleId . '"/>';
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
            $articleId = Post::get('edit-article-id');
            $title     = Post::get('edit-article-title');
            $author    = Post::get('edit-article-author');
            $content   = Post::get('edit-article-content');

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

            $newsArticleArray = $this->getNewsArticle();
            $newsArticle      = $newsArticleArray[$articleId];

            $html = $this->editArticleHtml($newsArticle, $articleId);
            echo $html;
        }

        die;
    }

    /**
     * delete from news_table by specified id
     * 
     * @return void
     */
    public function removeArticle() {
        $articleId = Post::get('remove-article-id');

        $query = $this->_dbh->prepare(sprintf(
            "DELETE 
               FROM %s
              WHERE news_id = '%s'",
            DbFactory::TABLE_NEWS,
            $articleId
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
}