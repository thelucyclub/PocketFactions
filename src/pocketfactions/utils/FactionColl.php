<?php

namespace pocketfactions\utils;

use pocketfactions\faction\Faction;
use pocketfactions\PocketFactions;

class FactionColl{
	/** @var PocketFactions */
	private $plugin;
	private $sql;
	/** @var Faction[] */
	private $factions = [];
	public function __construct(PocketFactions $plugin){
		$this->plugin = $plugin;
		$this->sql = new \SQLite3(":memory:");
		$this->sql->exec("CREATE TABLE factions (id INTEGER PRIMARY KEY, name TEXT COLLATE NOCASE, open INTEGER, lastactive INTEGER);");
		$this->sql->exec("CREATE TABLE factions_players (player TEXT PRIMARY KEY COLLATE NOCASE, id INTEGER);");
		$this->sql->exec("CREATE TABLE factions_chunks (x INTEGER, z INTEGER, id INTEGER) WITHOUT ROWID;");
	}
	public function unloadAll(){
		$this->factions = [];
		$this->sql->exec("DELETE FROM factions;");
		$this->sql->exec("DELETE FROM factions_players;");
		$this->sql->exec("DELETE FROM factions_chunks;");
	}
	public function loadFaction(Faction $faction){
		$this->sql->exec("INSERT INTO factions (id, name, open, lastactive) VALUES ({$faction->getId()}, '{$this->sql->escapeString($faction->getName())}', {$faction->getOpenStatus()}, {$faction->getLastActive()}");
	}
}
