<?php

namespace pocketfactions\db;

use pocketfactions\PocketFactions;

class DummyDatabase implements Database{
	private $plugin;
	private $nextId = 0;
	public function __construct(PocketFactions $plugin){
		$this->plugin = $plugin;
	}
	public function getPlugin(){
		return $this->plugin;
	}
	public function nextId(){
		return $this->nextId++;
	}
	/**
	 * Load all factions from the database and call PocketFactions::setFactions(Faction[])
	 * @param bool $async default true; whether to load with an asynchrnous task
	 */
	public function loadAll($async = true){
		$this->plugin->setFactions([]);
	}
	/**
	 * @param \pocketfactions\faction\Faction[] $factions
	 * @param bool $async default true
	 */
	public function saveAll(array $factions, $async = true){

	}
}
