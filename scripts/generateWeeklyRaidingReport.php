<?php

include 'script.php';

class WeeklyRaidingReport extends Script {
    protected static $encountersKilledArray;
    protected static $latestDate;
    protected static $latestDateStrtotime;
    protected static $earliestDate;
    protected static $earliestDateStrtotime;
    protected static $databaseDate;
    protected static $reportArticle;

    public static function init() {
        Logger::log('INFO', 'Starting Generate Weekly Raiding Report...');

        self::$encountersKilledArray = array();
        self::$latestDateStrtotime   = strtotime('now');
        self::$latestDate            = date('m/d/Y', self::$latestDateStrtotime);
        self::$earliestDateStrtotime = strtotime('-7 days', self::$latestDateStrtotime);
        self::$earliestDate          = date('m/d/Y', self::$earliestDateStrtotime);
        self::$databaseDate          = date('Y-m-d', self::$latestDateStrtotime) .' 00:00:00';

        self::getPastWeekEncounters();
        self::createArticle();
        self::displayArticle();

        Logger::log('INFO', 'Adding new Weekly Raiding Report to Database...');
        self::addArticleToDatabase();

        Logger::log('INFO', 'Sending Social Media Update Posts...');
        self::createSocialMediaPosts();

        Logger::log('INFO', 'Generate Weekly Raiding Report Complete!');
    }

    public static function getPastWeekEncounters() {
        foreach ( CommonDataContainer::$guildArray as $guildId  => $guildDetails ) {
            $guildDetails->generateEncounterDetails('');

            // Loop through all Guilds and get All Encounter Kills
            foreach ( $guildDetails->_encounterDetails as $encounterId => $encounterDetails ) {
                if ( $encounterDetails->_strtotime >= self::$earliestDateStrtotime && $encounterDetails->_strtotime <= self::$latestDateStrtotime ) {
                    self::$encountersKilledArray[$encounterId][$guildId] = $encounterDetails->_strtotime;
                }
            }

            // Sorts Encounters by Latest Encounter
            krsort(self::$encountersKilledArray);

            foreach ( self::$encountersKilledArray as $encounterId => $guildArray ) {
                // Sort the guild array of the encounter by strtotime value and placing it back in the array
                asort($guildArray);
                self::$encountersKilledArray[$encounterId] = $guildArray;
            }
        }
    }

    public static function createArticle() {
        $articleDetails               = array();
        $articleDetails['date_added'] = self::$databaseDate;
        $articleDetails['content']    = '';
        $articleDetails['title']      = 'Raiding Report: ' . self::$earliestDate . ' - ' . self::$latestDate;
        $articleDetails['added_by']   = 'News Bot';
        $articleDetails['type']       = 0;

        self::$reportArticle = new Article($articleDetails);

        foreach ( self::$encountersKilledArray as $encounterId => $guildArray ) {
            $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
            $dungeonDetails   = CommonDataContainer::$dungeonArray[$encounterDetails->_dungeonId];
            $numOfGuilds      = count($guildArray);

            $hyperLink = Functions::generateInternalHyperlink('standings', $encounterDetails, 'world', $encounterDetails->_name, '');

            self::$reportArticle->content .= '<h3>' . $hyperLink . ' (' . $numOfGuilds . ')</h3>';
            self::$reportArticle->content .= '<ul>';

                foreach ( $guildArray as $guildId => $strtotime ) {
                    $guildDetails  = CommonDataContainer::$guildArray[$guildId];

                    self::$reportArticle->content .= '<li>' . $guildDetails->_nameLink .' @ ' . $guildDetails->_encounterDetails->$encounterId->_datetime . '</li>';
                }

            self::$reportArticle->content .= '</ul>';
            
        }
    }

    public static function displayArticle() {
        echo '<html>';
            foreach( glob('../public/css/*.css' ) as $fileName) {
                echo '<link rel="stylesheet" type="text/css" href="' . $fileName . '">';
            }

            echo '<div style="width:1000px;">';
                echo '<div id="articles-wrapper">';
                    echo '<div class="article">';
                        echo '<div class="article-header">';
                            echo '<div>' . self::$reportArticle->title . '</div>';
                            echo '<span>Posted by: ' . self::$reportArticle->postedBy . ' @ ' . self::$reportArticle->date . '</span>';
                        echo '</div>';
                        echo '<div class="article-content">';
                            echo '<p>' . self::$reportArticle->content . '</p>';
                        echo '</div>';
                    echo '</div>';
                    echo '<div class="vertical-separator"></div>';
                echo '</div>';
            echo '</div>';
        echo '</html>';
    }

    public static function addArticleToDatabase() {
        $dbh = DbFactory::getDbh();

        $query = $dbh->prepare(sprintf(
            "INSERT INTO %s
            (title, content, date_added, added_by, published, type)
            values('%s','%s','%s','%s','%s','%s')",
             DbFactory::TABLE_NEWS,
             self::$reportArticle->title,
             mysql_real_escape_string(self::$reportArticle->content),
             self::$reportArticle->date,
             self::$reportArticle->postedBy,
             1,
             0
            ));

        $query->execute();
    }

    public static function createSocialMediaPosts() {
        /*
        Logger::log('INFO', 'Posting to Google+...');
        create_post_google($news_details['title'], 1);

        Logger::log('INFO', 'Posting to Facebook...');
        create_post_facebook($news_details['title'], 1);

        Logger::log('INFO', 'Posting to Twitter...');
        create_post_twitter($news_details['title'], 1);
        */
    }
}

WeeklyRaidingReport::init();