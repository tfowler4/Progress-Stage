<?php

/**
 * static class containing commonly used data arrays
 */
class CommonDataContainer {
    public static $guildArray;
    public static $regionArray;
    public static $serverArray;
    public static $tierArray;
    public static $dungeonArray;
    public static $encounterArray;
    public static $countryArray;
    public static $factionArray;
    public static $rankSystemArray;
    public static $twitchArray;
    public static $raidSizeArray;
    public static $tierRaidSizeArray;
    public static $daysArray;
    public static $monthsArray;
    public static $yearsArray;
    public static $hoursArray;
    public static $minutesArray;
    public static $timezonesArray;
    public static $encounterKillsArray;
    public static $recentRaidsArray;

    /**
     * initialize
     * 
     * @return void
     */
    public static function init() {
        self::$guildArray        = array();
        self::$regionArray       = array();
        self::$serverArray       = array();
        self::$tierArray         = array();
        self::$dungeonArray      = array();
        self::$encounterArray    = array();
        self::$countryArray      = array();
        self::$factionArray      = array();
        self::$rankSystemArray   = array();
        self::$twitchArray       = array();
        self::$raidSizeArray     = array();
        self::$tierRaidSizeArray = array();
        self::$daysArray         = array();
        self::$monthsArray       = array();
        self::$yearsArray        = array();
        self::$hoursArray        = array();
        self::$minutesArray      = array();
        self::$timezonesArray    = array();
        self::$recentRaidsArray  = array();
    }
}

CommonDataContainer::init();