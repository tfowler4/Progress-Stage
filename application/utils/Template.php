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
       
        foreach( $tableHeader as $tableHeaderValues ) {
            $html .= '<th class="subHeader ' . $tableHeaderValues['class'];

            $html .= '">' . $tableHeaderValues['header'] . '</th>';

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
        $html .= '<th class="subTitle bg-primary" colspan="' . $columnSpan . '">';
        $html .= '<div class="pull-left">' . $headerText . '</div>';

        if ( !empty($optionText) ) {
            $html .= '<div class="pull-right">';
            $html .= '<a data-toggle="modal" data-target="#spreadsheetModal" data-dungeon-id="' . $optionId . '" class="btn btn-default btn-xs ' . $optionClass . '" href="#"><span class="glyphicon glyphicon-th-list"></span></a>';
            $html .= '</div>';
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

        $html .= '<tr class="table-data">';

        foreach( $tableHeader as $tableHeaderValues ) {
            $value       = $tableHeaderValues['key'];
            $columnValue = $columnObject->$value;

            $html .= '<td';

            $html .= ' class="' . $tableHeaderValues['class'] . '"';

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
                                    $columnValue = '--';
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

        $html .= '<div class="panel panel-primary">';
        $html .= '<div class="panel-heading"><span class="h4">Glossary</span></div>';
        $html .= '<table class="table table-condensed">';
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

        $html = '<div class="row">';

        foreach ( $topGuildArray as $guildId => $guildDetails ) {
            $placeStr = '';

            if ( !empty($guildProperties) ) {
                foreach( $guildProperties as $property) {
                    if ( empty($placeStr) ) {
                        $placeStr = $guildDetails->$property;
                    } else {
                        $placeStr .= ' ' . $guildDetails->$property;
                    }
                }
            }

            $html .= '<div class="col-lg-4 col-md-4 col-sm-4 text-center">';
                $html .= '<div class="thumbnail top-guild">';
                    $html .= '<a href="' . Functions::generateInternalHyperLink('guild', $guildDetails->_faction, $guildDetails->_server, $guildDetails->_name, '', false) . '">' . self::getLogo($guildDetails) . '</a>';
                $html .= '</div>';
                    $html .= '<h3><strong>';
                        $html .= Functions::getImageFlag($guildDetails->_country, 'medium') . ' <span>' . Functions::shortName($guildDetails->_name, 20);
                    $html .= '</span></strong></h3>';
                    
                    $html .= '<p><small style="font-size:100%;">';
                        if ( !empty($placeStr) && in_array('_dateCreated', $guildProperties) ) {
                            $html .= 'Joined ' . $placeStr;
                        } elseif ( !empty($placeStr) ) {
                            $html .= Functions::convertToOrdinal($guildCount). ' - ' . $placeStr;
                        }
                    $html .= '</small></p>';
            $html .= '</div>';

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
            $class           = 'class="img-responsive"';

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
                $class              ='class="img-responsive"';

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
            'World First' => '<span class="world-first-rank">First guild to complete encounter in the world</span>',
            'Region First' => '<span class="region-first-rank">First guild to complete encounter in the region</span>'
            );

        $html = '';
        $html .= '<div class="row">';
        $html .= '<div class="col-lg-6 col-md-6 col-sm-6 col-xs-8">';
        $html .= self::drawGlossary($glossaryArray, 1);
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<div class="row">';
        $html .= '<div class="   ">';
        $html .= '<div class="panel panel-primary">';
        $html .= '<table class="table table-striped table-hover table-condensed">';
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
                array('header' => 'Rank',   'key' => '_rank',       'class' => 'text-center'),
                array('header' => 'Guild',  'key' => '_nameLink',   'class' => ''),
                array('header' => 'Server', 'key' => '_serverLink', 'class' => '')
            );

        // Generate TableHeader of Encounters
        $encounterArray = array();

        foreach ( (array)$dungeonDetails->_encounters as $encounterId => $encounterDetails) {
            $header = $encounterDetails->_encounterShortName;
            $key    = '_encounterDetails->'. $encounterId . '->_datetime';
            $class  = 'text-center';
            $arr = array('header' => $header, 'key' => $key, 'class' => $class);
            array_push($tableHeader, $arr);
        }

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
        $html .= '<option value="">Select Encounter</option>';

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
                array('header' => 'Notes', 'key' => '_notes', 'class' => ''),
                array('header' => 'URL', 'key' => '_url', 'class' => ''),
                array('header' => 'Action', 'key' => '_videoLink', 'class' => 'text-center')
            );

        $query = $dbh->prepare(sprintf(
            "SELECT video_id,
                    guild_id,
                    encounter_id,
                    url,
                    type,
                    notes
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

        $html .= '<div class="   ">';
        $html .= '<div class="panel panel-primary">';
        $html .= '<table class="table table-striped table-hover table-condensed">';
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
                    $html = '<li>';

                    if ( $isHyperlink ) { $html .= Functions::generateInternalHyperlink('news', '', '', '<span class="glyphicon glyphicon-home"></span>', ''); }

                    $html .= '</li>';
                }
                break;
            case 'quickSubmit':
                if ( MODULE_QUICKSUB_SET == 1 ) {
                    $html = '<li class="modal-activator" data-toggle="modal" data-target="#quickModal"><a href="#"><span class="glyphicon glyphicon-upload"></span>  Submit a Kill</a></li>';
                }
                break;
            case 'howto':
                if ( MODULE_HOWTO_SET == 1 ) {
                    $html = '<li>';

                    if ( $isHyperlink ) { $html .= Functions::generateInternalHyperlink('howto', '', '', '<span class="glyphicon glyphicon-check"></span>  FAQ', ''); }

                    $html .= '</li>';
                }
                break;
            case 'register':
                if ( MODULE_REGISTER_SET == 1 ) {
                    if ( !isset($_SESSION['logged']) ) {
                        $html = '<li>';

                        if ( $isHyperlink ) { $html .= Functions::generateInternalHyperlink('register', '', '', '<span class="glyphicon glyphicon-user"></span>  Register', ''); }

                        $html .= '</li>';
                    }
                }
                break;
            case 'contactus':
                if ( MODULE_CONTACT_SET == 1 ) {
                    $html = '<li class="modal-activator" data-toggle="modal" data-target="#contactModal"><a href="#"><span class="glyphicon glyphicon-envelope"></span> Contact Us</a></li>';
                }
                break;
            case 'userpanel':
                if ( MODULE_USERPANEL_SET == 1 ) {
                    if ( isset($_SESSION['logged']) && $_SESSION['logged'] == 'yes' ) {
                        $html = '<li>';

                        if ( $isHyperlink ) { $html .= Functions::generateInternalHyperlink('userpanel', '', '', '<span class="glyphicon glyphicon-wrench"></span>  Dashboard', ''); }

                        $html .= '</li>';
                    }
                }
                break;
            case 'directory':
                if ( MODULE_DIRECTORY_SET == 1 ) {
                    $html = '<li>';

                    if ( $isHyperlink ) { $html .= Functions::generateInternalHyperlink('directory', '', '', '<span class="glyphicon glyphicon-tasks"></span>  Directory', ''); }

                    $html .= '</li>';
                }
                break;
            case 'login':
                if ( MODULE_LOGIN_SET == 1 ) {
                    if ( !isset($_SESSION['logged']) ) {
                        $html = '<li class="modal-activator" data-toggle="modal" data-target="#loginModal"><a href="#"><span class="glyphicon glyphicon-log-in"></span>  Login</a></li>';
                    }
                }
                break;
            case 'logout':
                if ( MODULE_LOGOUT_SET == 1 ) {
                    if ( isset($_SESSION['logged']) && $_SESSION['logged'] == 'yes' ) {
                        $html = '<li class="modal-activator" data-toggle="modal" data-target="#logoutModal"><a href="#"><span class="glyphicon glyphicon-log-out"></span>  Logout</a></li>';
                    }
                }
                break;
            case 'search':
                if ( MODULE_SEARCH_SET == 1 ) {
                    $html = '<li class="modal-activator" data-toggle="modal" data-target="#searchModal"><a href="#"><span class="glyphicon glyphicon-search"></span>  Search</a></li>';
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
                $html .= '<li class="dropdown">';
                    $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false">';

                    if ( $modelName == 'standings') { $html .= '<span class="glyphicon glyphicon-list"></span>  Standings '; }
                    if ( $modelName == 'rankings') { $html .= '<span class="glyphicon glyphicon-stats"></span>  Rankings '; }

                    $html .= '<span class="caret"></span></a>';
                    $html .= '<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">';
                        $currentEra = '';
                        $tierLevel  = 0;
                        foreach( $topLevelArray as $tierId => $tierDetails) {
                            if ( $currentEra == '' || $currentEra != $tierDetails->_era ) {
                                $currentEra = $tierDetails->_era;

                                if ( $tierLevel > 0 ) {
                                    $html .= '<li role="separator" class="divider"></li>';
                                }

                                $html .= '<li class="dropdown-header">' . $currentEra . '</li>';
                            }

                            $html .= '<li class="dropdown-submenu dropdown-first-level">';
                                $html .= '<a tabindex="-1" href="#" class="tab-index">';
                                    $html .= $tierDetails->_altTier . ' - ' . $tierDetails->_name;
                                $html .= '</a>';
                                $html .= '<ul class="dropdown-menu">';
                                    foreach( $tierDetails->_dungeons as $dungeonId => $dungeonDetails ) {
                                        if ( $modelName == 'standings') { $html .= '<li class="dropdown-submenu">' . Functions::generateInternalHyperlink('standings', $dungeonDetails, 'world', $dungeonDetails->_name, ''); }
                                        if ( $modelName == 'rankings' ) { $html .= '<li class="dropdown-link">' . Functions::generateInternalHyperlink('rankings', $dungeonDetails, 'world/' . POINT_SYSTEM_DEFAULT, $dungeonDetails->_name, ''); }
                                            if ( $numOfLevels > 2 ) {
                                                $html .= '<ul class="dropdown-menu">';

                                                    $currentType     = '';
                                                    $encounterLevel  = 0;
                                                    foreach( $dungeonDetails->_encounters as $encounterId => $encounterDetails ) {
                                                        if ( $currentType == '' || $currentType != $encounterDetails->_type ) {
                                                            $currentType = $encounterDetails->_type;
                                                            $title = '';

                                                            switch ($currentType) {
                                                                case '0':
                                                                    $title = 'Normal Encounters';
                                                                    break;
                                                                case '1':
                                                                    $title = 'Achievements';
                                                                    break;
                                                                case '2':
                                                                    $title = 'Hard Mode / Special Encounters';
                                                                    break;
                                                            }

                                                            if ( $encounterLevel > 0 ) {
                                                                $html .= '<li role="separator" class="divider"></li>';
                                                            }

                                                            $html .= '<li class="dropdown-header">' . $title . '</li>';
                                                        }

                                                        if ( $modelName == 'standings') { $html .= '<li class="dropdown-link">' . Functions::generateInternalHyperlink('standings', $encounterDetails, 'world', $encounterDetails->_name, '') . '</li>'; }

                                                        $encounterLevel++;
                                                    }
                                                $html .= '</ul>';
                                            }
                                        $html .= '</li>';
                                    }
                                $html .= '</ul>';
                            $html .= '</li>';

                            $tierLevel++;
                        }
                    $html .= '</ul>';
                $html .= '</li>';
                break;
            case 'servers':
                $html .= '<li class="dropdown">';
                    $html .= '<a href="#" class="dropdown-toggle" data-toggle="dropdown" role="button" aria-haspopup="true" aria-expanded="false"><span class="glyphicon glyphicon-globe"></span>  Servers <span class="caret"></span></a>';
                    $html .= '<ul class="dropdown-menu multi-level" role="menu" aria-labelledby="dropdownMenu">';
                        foreach( $topLevelArray as $regionId => $regionDetails ) {
                            $html .= '<li class="dropdown-submenu">';
                                $html .= '<a tabindex="-1" href="#" class="tab-index">';
                                    $html .= $regionDetails->_regionImage . '<span>' . $regionDetails->_name . '</span>';
                                $html .= '</a>';
                                $html .= '<ul class="dropdown-menu">';
                                    foreach( $regionDetails->_servers as $serverId => $serverDetails ) {
                                        if ( $serverDetails->_region != $regionDetails->_abbreviation ) { continue; }

                                        $html .= '<li class="dropdown-link">' . $serverDetails->_navLink . '</li>';
                                    }
                                $html .= '</ul>';
                            $html .= '</li>';
                        }
                    $html .= '</ul>';
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

    /**
     * get the necessary form html through given form id
     * 
     * @param  string $formId [ id of form ]
     * 
     * @return void
     */
    public static function loadModalHtml($formId) {
        include_once ABS_FOLD_TEMPLATES . $_SESSION['template'] . '/forms.html';
    }

    public static function getSearchResults($searchTerm) {
        $searchResults = GuildSearch::getSearchResults($searchTerm);

        $html = '';
        $html .= '<div class="panel panel-primary">';
        $html .= '<div class="panel-heading">Search Results</div>';
        $html .= '<table class="table table-condensed table-hover table-striped">';
        $html .= '<thead>';
        $html .= '<tr>';
        $html .= '<th>Guild</th>';
        $html .= '<th>Server</th>';
        $html .= '<th>Region</th>';
        $html .= '</tr>';
        $html .= '</thead>';
        $html .= '<tbody>';

        if ( !empty($searchResults) ) {
            foreach( $searchResults as $guildId => $guildDetails ) {
                $html .= '<tr>';
                $html .= '<td>' . $guildDetails->_nameLink . '</td>';
                $html .= '<td>' . $guildDetails->_serverLink . '</td>';
                $html .= '<td>' . $guildDetails->_region . '</td>';
                $html .= '</tr>';
            }
        } else {
            $html .= '<tr><td colspan="3" class="text-center">No search results found.</td></tr>';
        }

        $html .= '</tbody>';
        $html .= '</table>';
        $html .= '</div>';

        return $html;
    }
}