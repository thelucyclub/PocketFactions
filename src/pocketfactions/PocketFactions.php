<?php

namespace pocketfactions;

use pocketfactions\db\Database;
use pocketfactions\utils\FactionColl;
use pocketfactions\utils\Options;
use pocketmine\plugin\PluginBase;

class PocketFactions extends PluginBase{
	/** @var Options */
	private $opts;
	/** @var Database */
	private $db;
	/** @var FactionColl */
	private $fcoll;
	public function onEnable(){
		$this->saveDefaultConfig();
		$this->opts = new Options($this);
		$this->fcoll = new FactionColl($this);
	}
	public function getDatabase(){
		return $this->db;
	}
	public function setDatabaseAndReload(Database $db){
		$this->db = $db;
		$this->db->loadAll(false);
	}
	/**
	 * @return Options
	 */
	public function getOpts(){
		return $this->opts;
	}
	/**
	 * @param faction\Faction[] $factions
	 */
	public function setFactions(array $factions){
		$this->fcoll->unloadAll();
		foreach($factions as $faction){
			$this->fcoll->loadFaction($faction);
		}
	}
}
