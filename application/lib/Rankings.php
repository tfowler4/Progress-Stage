<?php

/**
 * rankings object to handle the creation and updating process of guild dungeon/encounter rankings
 */
class Rankings {
    protected static $_currentQPDungeonRankings  = array();
    protected static $_currentAPDungeonRankings  = array();
    protected static $_currentAPFDungeonRankings = array();
    protected static $_currentEncounterStandings = array();
    protected static $_killEntry;
    protected static $_guildDetails;
    protected static $_killTime;

	public static function create() {

	}

	public static function update($guildId, $encounterId, $dungeonId) {
        // get rankings in array format, unsorted $guildId => qp_points = 'xxxxx'
		self::_getExistingRankings($encounterId, $dungeonId);

        // get guild details
        self::$_guildDetails = self::$_currentEncounterStandings[$guildId];

		// get kill details
		self::_setKillEntry($encounterId);

		// set kill time
		self::$killTime = self::$_guildDetails->strtotime;



		print_r(self::$_guildDetails->_encounterDetails);
        echo ' <br>Load Time: '.(round((microtime(true) - $_SERVER["REQUEST_TIME_FLOAT"])/1, 2)).' seconds.'; 
        echo ' <br>Memory Usage: ' .round(memory_get_usage(true)/1048576,2) . 'mb';
	}

	protected static function _setKillEntry($encounterId) {
		self::$_guildDetails->generateEncounterDetails('encounter', $encounterId);
	}

    protected static function _getExistingRankings($encounterId, $dungeonId) {
        self::$_currentEncounterStandings = DbFactory::getStandingsForEncounter($encounterId);

        self::$_currentQPDungeonRankings  = DbFactory::getRankingsForDungeon($dungeonId, 'qp', 'world');
        self::$_currentAPDungeonRankings  = DbFactory::getRankingsForDungeon($dungeonId, 'ap', 'world');
        self::$_currentAPFDungeonRankings = DbFactory::getRankingsForDungeon($dungeonId, 'apf', 'world');
        //self::$_currentEncounterStandings = DbFactory::getStandingsForDungeon($dungeonId);

    	/*
        $dbh = DbFactory::getDbh();

        // getting all dungeon rankings
        $query = $dbh->query(sprintf(
            "SELECT *
               FROM %s
              WHERE dungeon_id = %d" , 
                    DbFactory::TABLE_RANKINGS,
                    $dungeonId
        ));

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId = $row['guild_id'];

            self::$_currentDungeonRankings[$guildId] = $row;
        }

        // getting all encounter rankings
        $query = $dbh->query(sprintf(
            "SELECT *
               FROM %s
              WHERE encounter_id = %d",
                    DbFactory::TABLE_ENCOUNTER_RANKINGS,
                    $encounterId
        ));

        while ( $row = $query->fetch(PDO::FETCH_ASSOC) ) {
            $guildId = $row['guild_id'];

            self::$_currentEncounterRankings[$guildId] = $row;
        }
        */
    }
}