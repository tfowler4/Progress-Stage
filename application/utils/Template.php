<?php
class Template {
    public static function init() {}

    public static function drawEmptyGuildTableRow($tableHeader, $rowText) {
        $html = '';
        $html .= '<tr>';
        $html .= '<td colspan="' . count($tableHeader) . '">' . $rowText . '</td>';
        $html .= '</tr>';

        return $html;
    }

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

    public static function getLogo($guildDetails) {
        $logo     = '';
        $src      = FOLD_GUILD_LOGOS . 'logo-' . $guildDetails->_guildId;
        $localSrc = ABSOLUTE_PATH . '/public/images/' . strtolower(GAME_NAME_1) . '/guilds/logos/logo-' . $guildDetails->_guildId;

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

    public static function getScreenshot($guildDetails, $encounterDetails) {
        $screenshot = '';

        if ( !empty($encounterDetails) ) {
            $encounterId = $encounterDetails->_encounterId;
            $identifier  = $guildDetails->_guildId . '-' . $encounterId;
            $src         = FOLD_KILLSHOTS . $identifier;
            $localSrc    = ABSOLUTE_PATH . '/public/images/' . strtolower(GAME_NAME_1) .  '/screenshots/killshots/' . $identifier;

            if ( file_exists($localSrc) && getimagesize($localSrc) ) {
                $imageDimensions    = getimagesize($localSrc);
                $class              = '';

                if ( $imageDimensions[0] > 600 ) { 
                    $class = 'class="screenshot-large"'; 
                }

                $screenshot  = '<img src="' . $src . '" ' . $class . '>';
            }
        }

        return $screenshot;
    }

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

        foreach( $spreadsheet->listArray as $dataType => $dataObject ) {
            $html .= self::drawSubTitleTableRow($tableHeader, $dungeonDetails->_name . ' ' . $dataObject->header . ' (Encounter Spreadsheet)');

            if ( !empty($dataObject->data) ) {
                $html .= self::drawSubHeaderTableRow($tableHeader);

                foreach ( $dataObject->data as $guildId => $guildDetails ) {
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

    public static function getSpreadsheet($dungeonData) {
        $html        = '';
        $tableHeader = '';
        $view        = 'world';
        $dungeonData = explode('-', $dungeonData);
        $dungeonId   = $dungeonData[0];

        if ( isset($dungeonData[1]) ) { $view = $dungeonData[1]; }

        $dungeonDetails = CommonDataContainer::$dungeonArray[$dungeonId];

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

        $params   = array();
        $params[0] = $dungeonId;
        $params[1] = $view;

        $spreadsheet = new Listings('standings', $params);

        $html .= self::getSpreadsheetHtml($tableHeader, $spreadsheet, $dungeonDetails);

        return $html;
    }

    public static function getEncounterDropdownListHtml($guildId) {
        $guildDetails = CommonDataContainer::$guildArray[$guildId];
        $guildDetails->generateEncounterDetails('');

        $html = '';

        foreach( CommonDataContainer::$encounterArray as $encounterId => $encounterDetails) {
            if( !isset($guildDetails->_encounterDetails->$encounterId) ) {
                $html .= '<option value="' . $encounterId . '">' . $encounterDetails->_dungeon . ' - ' . $encounterDetails->_encounterName . '</option>';
            }
        }

        return $html;
    }

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
                    $html .= '<li id="search-activator" class="no-highlight">' . $GLOBALS['images']['search'] . '</li>';
                }
                break;
        }

        return $html;
    }

    public static function drawHeaderMenuDropdownItem($modelName, $isHyperlink, $numOfLevels, $topLevelArray) {
        $html  = '';

        switch ($modelName) {
            case 'standings':
            case 'rankings':
                $html .= '<li>';
                    if ( $modelName == 'standings') { $html .= 'Progression Standings ' . $GLOBALS['images']['icon-dropdown']; }
                    if ( $modelName == 'rankings') { $html .= 'Point Rankings ' . $GLOBALS['images']['icon-dropdown']; }
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
                                                if ( $dungeonDetails->_numOfEncounters > 0 ) { $html .= $GLOBALS['images']['icon-expand']; }
                                            }
                                        $html .= '</div>';
                                    }
                                $html .= '</div>';
                                if ( $tierDetails->_numOfDungeons > 0 ) { $html .= $GLOBALS['images']['icon-expand']; }
                            $html .= '</div>';
                        }
                    $html .= '</div>';
                $html .= '</li>';
                break;
            case 'servers':
                $html .= '<li>';
                    $html .= 'Servers ' . $GLOBALS['images']['icon-dropdown'];
                    $html .= '<div class="dropdown-menu-first-level">';
                        foreach( $topLevelArray as $regionId => $regionDetails ) {
                            $html .= '<div class="dropdown-menu-item">';
                                $html .= $regionDetails->_regionImage . '<span style="vertical-align:middle;">' . $regionDetails->_name . '</span>';
                                $html .= '<div class="dropdown-menu-second-level image">';
                                    foreach( $regionDetails->_servers as $serverId => $serverDetails ) {
                                        if ( $serverDetails->_region != $regionDetails->_abbreviation ) { continue; }
                                        $html .= '<div class="dropdown-menu-item">' . $serverDetails->_nameLink . '</div>';
                                    }
                                $html .= '</div>';
                                if ( $regionDetails->_numOfServers > 0 ) { $html .= $GLOBALS['images']['icon-expand']; }
                            $html .= '</div>';
                        }
                    $html .= '</div>';
                $html .= '</li>';
                break;
        }

        return $html;
    }

    public static function getPopupForm($formId) {
        include_once ABSOLUTE_PATH . '/public/templates/default/forms.html';
    }
}

Template::init();