<?php

/**
 * class for handling drawing commonly used template elements
 */
class Template {
    /**
     * draw empty table row that spans the length of tableheader columns
     * 
     * @param  array  $tableHeader [ list of header columns ]
     * @param  string $rowText     [ text to be displayed in row ]
     * 
     * @return string [ html string containing empty table row ]
     */
    public static function drawEmptyGuildTableRow($tableHeader, $rowText) {
        $html = '';
        $html .= '<tr>';
        $html .= '<td colspan="' . count($tableHeader) . '">' . $rowText . '</td>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * draw table subheader row
     * 
     * @param  array  $tableHeader [ list of header columns ]
     * 
     * @return string [ html string containing subheader table row ]
     */
    public static function drawSubHeaderTableRow($tableHeader) {
        $html      = '';
        $cellCount = 1;

        $html .= '<tr>';

        foreach( $tableHeader as $key => $value ) {
            $html .= '<th class="subHeader';

            if ( $cellCount % 3 == 0 ) {
                $html .= ' border-right';
            }

            $html .= '">' . $key . '</th>';

            $cellCount++;
        }

        $html .= '</tr>';

        return $html;
    }

    /**
     * draw table subtitle row
     * 
     * @param  array  $tableHeader [ list of header columns ]
     * @param  string $headerText  [ text to be in the table cell ]
     * @param  string $optionText  [ text to be on the right side of table cell ]
     * @param  string $optionId    [ id of optionText ]
     * 
     * @return string [ html string containing subtitle table row ]
     */
    public static function drawSubTitleTableRow($tableHeader, $headerText, $optionText = '', $optionId = '') {
        $columnSpan  = count($tableHeader);
        $optionClass = '';

        if ( !empty($optionText) ) {
            $optionClass = strtolower($optionText);
        }

        $html = '';
        $html .= '<tr>';
        $html .= '<th class="subTitle" colspan="' . $columnSpan . '">';
        $html .= $headerText;

        if ( !empty($optionText) ) {
            $html .= '<a id="' . $optionId . '" class="table-header-link move-right' . $optionClass . '" href="#">' . $optionText . '</a>';
        }

        $html .= '</th>';
        $html .= '</tr>';

        return $html;
    }

    /**
     * draw table body row
     * 
     * @param  array  $tableHeader  [ list of header columns ]
     * @param  object $columnObject [ object that contains the keys/values ]
     * @param  string $specialRules [ special ruleset definition ]
     * 
     * @return string [ html string containing a table row ]
     */
    public static function drawBodyTableRow($tableHeader, $columnObject, $specialRules = '') {
        $html        = '';
        $cellCount   = 1;
        $columnValue = '';

        $html .= '<tr>';

        foreach( $tableHeader as $key => $value ) {
            $columnValue = $columnObject->$value;

            $html .= '<td';

            if ( $cellCount % 3 == 0 ) {
                $html .= ' class="border-right"';
            } 

            if ( strpos($value, '->') > 0 ) {
                $objArray = explode('->', $value);
                $stdObj;

                foreach ( $objArray as $obj ) {
                    if ( empty($stdObj) ) {
                        $stdObj = $columnObject->$obj;
                    } else {
                        if ( property_exists($stdObj, $obj) ) {
                            $stdObj = $stdObj->$obj;
                        }
                    }
                }

                if ( !empty($stdObj) ) {
                    $columnValue = $stdObj;
                }
            }

            // If is still an object, convert to string temporary 'meh'
            if ( is_object($columnValue) ) {
                $columnValue = '--';
            }

            //Special Rules
            if ( !empty($specialRules) ) {
                switch ($specialRules) {
                    case 'spreadsheet':
                        // we need the guild details object given it default gives the dungeonDetails
                        $columnObject = CommonDataContainer::$guildArray[$columnObject->_guildId];

                        // Given this is dungeon standings only, we know the key value will always be _encounterDetails->encounterId->_datatime, get the encounterId
                        if ( strpos($value, '->') > 0 ) {
                            $columnDetails = explode('->', $value);

                            if ( $columnValue != '--' ) {
                                $class       = '';
                                $detailsName = $columnDetails[0];
                                $detailsId   = $columnDetails[1];

                                $objDetails  = $columnObject->$detailsName;

                                if ( isset($objDetails->$detailsId) ) {
                                    $objDetails  = $objDetails->$detailsId;

                                    // Set World/Region First Colors
                                    if ( $objDetails->_regionRank == 1 ) { $class = 'class = "region-first-rank"'; }
                                    if ( $objDetails->_worldRank == 1 ) { $class = 'class = "world-first-rank"'; }

                                    // If Screenshot does not exist
                                    if ( $objDetails->_screenshotLink == '--' ) {
                                        $columnValue = '<a ' . $class . ' href="#">' . $objDetails->_datetime . '</a>';
                                    } else {
                                        $columnValue = '<a ' . $class . ' href="' . FOLD_KILLSHOTS . $columnObject->_guildId . '-' . $columnDetails[1]  . '" rel="lightbox["kill_shots"]">' . $objDetails->_datetime . '</a>';
                                    }
                                } else {
                                    $columnValue = '--';;
                                }
                            }
                        }

                        break;
                }
            }

            $html .= '>' . $columnValue . '</td>';

            $cellCount++;
        }
        
        $html .= '</tr>';

        return $html;
    }

    /**
     * draw glossary box
     * 
     * @param  array   $glossaryArray [ array containing glossary key/values ]
     * @param  integer $numOfColumns  [ number of columsn]
     * 
     * @return string [ html string containing glossary table ]
     */
    public static function drawGlossary($glossaryArray, $numOfColumns) {
        $html        = '';
        $columnCount = 0;

        $html .= '<div id="glossary-wrapper">';
        $html .= '<table class="glossary">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th colspan="' . $numOfColumns . '">Glossary</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach ( $glossaryArray as $name => $definition ) {
            if ( $columnCount == 0 ) {
                $html .= '<tr>';
            } elseif( $columnCount > 0 && $columnCount % $numOfColumns == 0 ) {
                $html .= '</tr>';
                $html .= '<tr>';
            }

            $html .= '<td> ' . $name . ': ' . $definition . '</td>';
            $columnCount++;
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';
        $html .= '<div class="clear"></div>';
        $html .= '<div class="vertical-separator"></div>';

        return $html;
    }

    /**
     * draw top 3 guild content pane
     * 
     * @param  array $topGuildArray   [ array of all the guilds in the pane ]
     * @param  array $guildProperties [ array of the properties to display ]
     * 
     * @return string [ html string containing top guild pane ]
     */
    public static function drawTopGuildPane($topGuildArray, $guildProperties) {
        $html       = '';
        $guildCount = 1;

        $html = '<div id="top-guild-wrapper" class="noselect">';

        foreach ( $topGuildArray as $guildId => $guildDetails ) {
            $placeStr = '';

            foreach( $guildProperties as $property) {
                if ( empty($placeStr) ) {
                    $placeStr = $guildDetails->$property;
                } else {
                    $placeStr .= ' ' . $guildDetails->$property;
                }
            }

            $html .= '<div class="top-guild-separator"></div>';
            $html .= '<a href="' . Functions::generateInternalHyperLink('guild', '', $guildDetails->_server, $guildDetails->_name, '', false) . '">';
            $html .= '<div class="top-guild-container">';
            $html .= '<div class="top-guild-logo">' . self::getLogo($guildDetails) . '</div>';
            $html .= '<div class="vertical-separator"></div>';
            $html .= '<div class="top-guild-name">' . Functions::getImageFlag($guildDetails->_country, 'small') . '<span>' .$guildDetails->_name . '</span></div>';
            $html .= '<div class="top-guild-place">' . Functions::convertToOrdinal($guildCount). ' - ' . $placeStr . '</div>';
            $html .= '</div>';
            $html .= '</a>';
            $html .= '<div class="top-guild-separator"></div>';

            $guildCount++;
        }

        $html .= '</div>';

        return $html;
    }

    /**
     * get guild logo image html
     * 
     * @param  GuildDetails $guildDetails [ guild details object ]
     * 
     * @return string [ html string containing guild logo ]
     */
    public static function getLogo($guildDetails) {
        $logo     = '';
        $src      = FOLD_GUILD_LOGOS . 'logo-' . $guildDetails->_guildId;
        $localSrc = ABS_FOLD_SITE_GUILD_LOGOS . 'logo-' . $guildDetails->_guildId;

        if ( file_exists($localSrc) && getimagesize($localSrc) ) {
            $imageDimensions = getimagesize($localSrc);
            $class           = '';

            if ( $imageDimensions[0] > 300 ) { 
                $class = 'class="guild-logo-medium"'; 
            }

            $logo  = '<img src="' . $src . '" ' . $class . '>';
        }

        return $logo;
    }

    /**
     * get screenshot image html
     * 
     * @param  GuildDetails     $guildDetails     [ guild details object ]
     * @param  EncounterDetails $encounterDetails [ encounter details object ]
     * 
     * @return string [ html string containing screenshot ]
     */
    public static function getScreenshot($guildDetails, $encounterDetails, $resizable = false) {
        $screenshot = '';

        if ( !empty($encounterDetails) ) {
            $encounterId = $encounterDetails->_encounterId;
            $identifier  = $guildDetails->_guildId . '-' . $encounterId;
            $src         = FOLD_KILLSHOTS . $identifier;
            $localSrc    = ABS_FOLD_KILLSHOTS . $identifier;

            if ( file_exists($localSrc) && getimagesize($localSrc) ) {
                $imageDimensions    = getimagesize($localSrc);
                $class              = '';

                if ( $imageDimensions[0] > 600 ) { 
                    $class = 'class="screenshot-large"'; 
                }

                $screenshot = '<img src="' . $src . '" ' . $class . ' >';

                if ( $resizable ) {
                    $screenshot = '<a href=' . $src . ' rel="lightbox[\'kill_shots\']">' . $screenshot . '</a>';
                }
            }
        }

        return $screenshot;
    }

    /**
     * get the spreadsheet html
     * 
     * @param  array          $tableHeader    [ list of header columns ]
     * @param  Listings       $spreadsheet    [ spreadsheet listings ]
     * @param  DungeonDetails $dungeonDetails [ dungeon details object ]
     * 
     * @return string [ html string containing spreadsheet html ]
     */
    public static function getSpreadsheetHtml($tableHeader, $spreadsheet, $dungeonDetails) {
        $glossaryArray = array(
            'World Frist' => '<span class="world-first-rank">First guild to complete encounter in the world</span>',
            'Region First' => '<span class="region-first-rank">First guild to complete encounter in the region</span>'
            );

        $html = '';
        $html .= '<div class="vertical-separator"></div>';
        $html .= self::drawGlossary($glossaryArray, 1);
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<div class="table-wrapper">';
        $html .= '<table class="listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';

        foreach( $spreadsheet as $listType => $dataArray ) {
            $html .= self::drawSubTitleTableRow($tableHeader, $dungeonDetails->_name . ' ' . $dataArray->headerText . ' (Encounter Spreadsheet)');

            if ( !empty($dataArray->data) ) {
                $html .= self::drawSubHeaderTableRow($tableHeader);

                foreach ( $dataArray->data as $guildId => $guildDetails ) {
                    $html .= self::drawBodyTableRow($tableHeader, $guildDetails, 'spreadsheet');
                }
            } else {
                $html .= self::drawEmptyGuildTableRow($tableHeader, 'No guild data found.');
            }
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * get spreadsheet html from ajax request
     * 
     * @param  array $dungeonData [ dungeon data containing view and id ]
     * 
     * @return string [ html string containing spreadsheet html ]
     */
    public static function getSpreadsheet($dungeonData) {
        $html        = '';
        $tableHeader = '';
        $view        = 'world';
        $dungeonData = explode('-', $dungeonData);
        $dungeonId   = $dungeonData[0];

        if ( isset($dungeonData[1]) ) { $view = $dungeonData[1]; }

        $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];
        $tierDetails    = CommonDataContainer::$tierArray[$dungeonDetails->_tier];

        $tableHeader = array(
                'Rank'   => '_rank',
                'Guild'  => '_nameLink',
                'Server' => '_serverLink',
            );

        // Generate TableHeader of Encounters
        $encounterArray = array();

        foreach ( (array)$dungeonDetails->_encounters as $encounterId => $encounterDetails) {
            $encounterArray[$encounterDetails->_encounterShortName] = '_encounterDetails->'. $encounterId . '->_datetime';
        }

        $tableHeader = array_merge($tableHeader, $encounterArray);

        $params    = array();
        $params[] = $view;
        $params[] = Functions::cleanLink($tierDetails->_name);
        $params[] = Functions::cleanLink($dungeonDetails->_name);
        $params[] = 'spreadsheet';

        $spreadsheet = new Listings('standings', $params);

        // set header text for spreadsheet sub-headers
        switch ($view) {
            case 'world':
                $spreadsheet->listArray->world['world']->headerText = 'World Standings';
                break;
            case 'region':
                foreach( $spreadsheet->listArray->$view as $listType => $dataArray ) {
                    $regionDetails = CommonDataContainer::$regionArray[$listType];
                    $dataArray->headerText = $regionDetails->_style . ' Standings';
                }
                break;
            case 'server':
                foreach( $spreadsheet->listArray->$view as $listType => $dataArray ) {
                    $serverDetails = CommonDataContainer::$serverArray[$listType];
                    $dataArray->headerText = $serverDetails->_name . ' Standings';
                }
                break;
            case 'country':
                break;
        }

        $html .= self::getSpreadsheetHtml($tableHeader, $spreadsheet->listArray->$view, $dungeonDetails);

        return $html;
    }

    /**
     * get html select dropdown menu containing encountert names
     * 
     * @param  string $guildId [ id of a guild ]
     * 
     * @return string [ html string containing select dropdown with encounters ]
     */
    public static function getEncounterDropdownListHtml($guildId) {
        $guildDetails = CommonDataContainer::$guildArray[$guildId];
        $guildDetails = Functions::getAllGuildDetails($guildDetails);

        $html = '';

        foreach( CommonDataContainer::$encounterArray as $encounterId => $encounterDetails) {
            if( !isset($guildDetails->_encounterDetails->$encounterId) ) {
                $html .= '<option value="' . $encounterId . '">' . $encounterDetails->_dungeon . ' - ' . $encounterDetails->_encounterName . '</option>';
            }
        }

        return $html;
    }

    /**
     * get html table for videos of a specific encounter/guild
     * 
     * @param  string $guildId [ id of a guild ]
     * 
     * @return string [ html string containing select dropdown with encounters ]
     */
    public static function getVideoListHtml($guildId, $encounterId) {
        $html = '';

        $encounterDetails = CommonDataContainer::$encounterArray[$encounterId];
        $guildDetails     = CommonDataContainer::$guildArray[$guildId];

        $dbh        = DbFactory::getDbh();
        $videoArray = array();
        $tableHeader = array(
            '#'       => '_videoId',
            'Notes'   => '_notes',
            'URL'     => '_url',
            'Action'  => '_videoLink'
            );

        $query = $dbh->prepare(sprintf(
            "SELECT *
               FROM %s
              WHERE guild_id = %d
                AND encounter_id = %d", 
                    DbFactory::TABLE_VIDEOS, 
                    $guildId,
                    $encounterId
                ));
        $query->execute();

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $videoArray[$row['video_id']] = new VideoDetails($row);
        }

        $html .= '<div class="vertical-separator"></div>';
        $html .= '<div class="table-wrapper">';
        $html .= '<table class="listing">';
        $html .= '<thead>';
        $html .= '</thead>';
        $html .= '<tbody>';
        $html .= self::drawSubTitleTableRow($tableHeader, $guildDetails->_name . ' :: ' . $encounterDetails->_name . ' Kill Videos');
        $html .= self::drawSubHeaderTableRow($tableHeader);

        if ( !empty($videoArray) ) {
            foreach( $videoArray as $videoId => $videoDetails ) {
                $html .= self::drawBodyTableRow($tableHeader, $videoDetails);
            }
        } else {
            $html .= self::drawEmptyGuildTableRow($tableHeader, 'No videos found.');
        }
        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }

    /**
     * draw header top level menu item
     * 
     * @param  string  $modelName   [ name of active model ]
     * @param  boolean $isHyperlink [ value to determine if hyperlink styling is applied ]
     * 
     * @return string [ html containing header menu item ]
     */
    public static function drawHeaderMenuItem($modelName, $isHyperlink) {
        $html = '';

        switch ($modelName) {
            case 'news':
                if ( MODULE_NEWS_SET == 1 ) {
                    $html = '<li>News</li>';

                    if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('news', '', '', $html, ''); }
                }
                break;
            case 'quickSubmit':
                if ( MODULE_QUICKSUB_SET == 1 ) {
                    $html = '<li id="quick-activator" class="activatePopUp">Quick Submission</li>';

                    if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('quickSubmit', '', '', $html, ''); }
                }
                break;
            case 'howto':
                if ( MODULE_HOWTO_SET == 1 ) {
                    $html = '<li>How-To</li>';

                    if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('howto', '', '', $html, ''); }
                }
                break;
            case 'register':
                if ( MODULE_REGISTER_SET == 1 ) {
                    if ( !isset($_SESSION['logged']) ) {
                        $html = '<li>Register</li>';

                        if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('register', '', '', $html, ''); }
                    }
                }
                break;
            case 'contactus':
                if ( MODULE_CONTACT_SET == 1 ) {
                    $html = '<li id="contact-activator" class="activatePopUp">Contact Us</li>';

                    if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('contactus', '', '', $html, ''); }
                }
                break;
            case 'userpanel':
                if ( MODULE_USERPANEL_SET == 1 ) {
                    if ( isset($_SESSION['logged']) && $_SESSION['logged'] == 'yes' ) {
                        $html = '<li>Control Panel</li>';

                        if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('userpanel', '', '', $html, ''); }
                    }
                }
                break;
            case 'directory':
                if ( MODULE_CONTACT_SET == 1 ) {
                    $html = '<li>Guild Directory</li>';

                    if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('directory', '', '', $html, ''); }
                }
                break;
            case 'login':
                if ( MODULE_LOGIN_SET == 1 ) {
                    if ( !isset($_SESSION['logged']) ) {
                        $html = '<li id="login-activator" class="activatePopUp">Login</li>';

                        if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('login', '', '', $html, ''); }
                    }
                }
                break;
            case 'logout':
                if ( MODULE_LOGOUT_SET == 1 ) {
                    if ( isset($_SESSION['logged']) && $_SESSION['logged'] == 'yes' ) {
                        $html = '<li id="logout-activator" class="activatePopUp">Logout</li>';

                        if ( $isHyperlink ) { $html = Functions::generateInternalHyperlink('logout', '', '', $html, ''); }
                    }
                }
                break;
            case 'search':
                if ( MODULE_SEARCH_SET == 1 ) {
                    $html = '<li class="no-highlight"><form id="search-form"><input id="search-input" placeholder="Enter guild name" type="text" /></form></li>';
                    $html .= '<li id="search-activator" class="no-highlight">' . IMG_ICON_SEARCH . '</li>';
                }
                break;
        }

        return $html;
    }

    /**
     * draw second and third levels of header mouseover menu
     * 
     * @param  string  $modelName     [ name of active model ]
     * @param  boolean $isHyperlink   [ value to determine if hyperlink styling is applied ]
     * @param  itneger $numOfLevels   [ number of dropdown levels ]
     * @param  array   $topLevelArray [ array of content dropdown menu will display ]
     * 
     * @return string [ html string containing dropdown levels of header menu item ]
     */
    public static function drawHeaderMenuDropdownItem($modelName, $isHyperlink, $numOfLevels, $topLevelArray) {
        $html  = '';

        switch ($modelName) {
            case 'standings':
            case 'rankings':
                $html .= '<li>';
                    if ( $modelName == 'standings') { $html .= 'Progression Standings ' . IMG_ARROW_DROPDOWN; }
                    if ( $modelName == 'rankings') { $html .= 'Point Rankings ' . IMG_ARROW_DROPDOWN; }
                    $html .= '<div class="dropdown-menu-first-level">';
                        foreach( $topLevelArray as $tierId => $tierDetails) {
                            $html .= '<div class="dropdown-menu-item">';
                                $html .= '(T' . $tierDetails->_tier . '/' . $tierDetails->_altTier . ') ' . $tierDetails->_name;
                                $html .= '<div class="dropdown-menu-second-level">';
                                    foreach( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
                                        if ( $modelName == 'standings') { $html .= '<div class="dropdown-menu-item">' . Functions::generateInternalHyperlink('standings', $dungeonDetails, 'world', $dungeonDetails->_name, ''); }
                                        if ( $modelName == 'rankings' ) { $html .= '<div class="dropdown-menu-item">' . Functions::generateInternalHyperlink('rankings', $dungeonDetails, 'world/' . POINT_SYSTEM_DEFAULT, '<div class="dropdown-menu-item">' . $dungeonDetails->_name . '</div>', ''); }
                                            if ( $numOfLevels > 2 ) {
                                                $html .= '<div class="dropdown-menu-second-level">';
                                                    foreach( $dungeonDetails->_encounters as $encounterId => $encounterDetails ) {
                                                        if ( $modelName == 'standings') { $html .= Functions::generateInternalHyperlink('standings', $encounterDetails, 'world', '<div class="dropdown-menu-item">' . $encounterDetails->_name . '</div>', ''); }
                                                    }
                                                $html .= '</div>';
                                                if ( $dungeonDetails->_numOfEncounters > 0 ) { $html .= IMG_ARROW_EXPAND; }
                                            }
                                        $html .= '</div>';
                                    }
                                $html .= '</div>';
                                if ( $tierDetails->_numOfDungeons > 0 ) { $html .= IMG_ARROW_EXPAND; }
                            $html .= '</div>';
                        }
                    $html .= '</div>';
                $html .= '</li>';
                break;
            case 'servers':
                $html .= '<li>';
                    $html .= 'Servers ' . IMG_ARROW_DROPDOWN;
                    $html .= '<div class="dropdown-menu-first-level">';
                        foreach( $topLevelArray as $regionId => $regionDetails ) {
                            $html .= '<div class="dropdown-menu-item">';
                                $html .= $regionDetails->_regionImage . '<span>' . $regionDetails->_name . '</span>';
                                $html .= '<div class="dropdown-menu-second-level image">';
                                    foreach( $regionDetails->_servers as $serverId => $serverDetails ) {
                                        if ( $serverDetails->_region != $regionDetails->_abbreviation ) { continue; }
                                        $html .= '<div class="dropdown-menu-item">' . $serverDetails->_nameLink . '</div>';
                                    }
                                $html .= '</div>';
                                if ( $regionDetails->_numOfServers > 0 ) { $html .= IMG_ARROW_EXPAND; }
                            $html .= '</div>';
                        }
                    $html .= '</div>';
                $html .= '</li>';
                break;
        }

        return $html;
    }

    /**
     * get the necessary form html through given form id
     * 
     * @param  string $formId [ id of form ]
     * 
     * @return void
     */
    public static function getPopupForm($formId) {
        include_once ABS_FOLD_TEMPLATES . $_SESSION['template'] . '/forms.html';
    }
}