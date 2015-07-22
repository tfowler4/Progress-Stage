<?php 

class AdministratorModel extends Model {

	public function __construct($module, $params) {
		parent::__construct($module);
		
		if (isset($_POST['request']) ) {

			switch ($_POST['request']) {
				case "tier-add":
					$this->addNewTier();
					break;
				case "dungeon-add":
					$this->addNewDungeon();
					break;
				case "encounter-add":
					$this->addNewEncounter();
					break;
				case "guild-add":
					$this->addNewGuild();
					break;
			}
		}
		
	}

	public function addNewTier() {

		$tier 		= $_POST['form'][0]['value'];
		$altName 	= $_POST['form'][1]['value'];
		$tierName 	= $_POST['form'][2]['value'];
		$startDate	= $_POST['form'][5]['value'] . '-' . $_POST['form'][3]['value'] . '-' .$_POST['form'][4]['value'];

		$sqlString = sprintf(
			"INSERT INTO %s
			(tier, alt_tier, title, date_start)
			values('%s', '%s', '%s', '%s')",
			DbFactory::TABLE_TIERS,
			$tier,
			$altName,
			$tierName,
			$startDate
			);

		die;
	}

	public function addNewDungeon() {
		$dungeon 	= $_POST['form'][0]['value'];
		$tier 		= $_POST['form'][1]['value'];
		$numOfMobs 	= $_POST['form'][2]['value'];

		$sqlString = sprintf(
			"INSERT INTO %s
			(name, tier, mobs)
			values('%s', '%s', '%s')",
			DbFactory::TABLE_DUNGEONS,
			$dungeon,
			$tier,
			$numOfMobs
			);

		die;		
	}

	public function addNewEncounter() {
		$encounter 		= $_POST['form'][0]['value'];
		$dungeon 		= $_POST['form'][1]['value'];
		$tier 			= $_POST['form'][2]['value'];
		$raidSize 		= $_POST['form'][3]['value'];

		$sqlString = sprintf(
			"INSERT INTO %s
			(name, dungeon, tier, players)
			values('%s', '%s', '%s', '%s')",
			DbFactory::TABLE_ENCOUNTERS,
			$encounter,
			$dungeon,
			$tier,
			$raidSize
			);

		die;
	}

	public function addNewGuild() {
		$guild 		= $_POST['form'][0]['value'];
		$server		= $_POST['form'][1]['value'];
		$country 	= $_POST['form'][2]['value'];

		$sqlString = sprintf(
			"INSERT INTO %s
			(name, server, country)
			values('%s', '%s', '%s')",
			DbFactory::TABLE_GUILDS,
			$guild,
			$server,
			$country
			);

		die;
	}
}

?>