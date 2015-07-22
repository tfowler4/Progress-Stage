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
            $html .= '<a style="float:right;" id="' . $optionId . '" class="button-link ' . $optionClass . '" href="#">' . $optionText . '</a>';
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

        return $html;
    }

    public static function drawTopGuildPane($topGuildArray, $guildProperties) {
        $html       = '';
        $guildCount = 1;

        $html = '<div id="top-content-wrapper" class="noselect">';

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
        $html .= self::drawGlossary($glossaryArray, 1);
        $html .= '<div class="vertical-separator"></div>';
        $html .= '<div class="table-wrapper">';
        $html .= '<table class="standings">';
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

    public static function getPopupForm($formId) {
        include_once ABSOLUTE_PATH . '/public/templates/default/forms.html';
    }
}

Template::init();