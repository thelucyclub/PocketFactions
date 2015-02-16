<?php

namespace pocketfactions\utils;

use pocketfactions\db\DummyDatabase;
use pocketfactions\db\PFDBDatabase;
use pocketfactions\faction\FactionRank;
use pocketfactions\faction\FactionRelation;
use pocketfactions\PocketFactions;

class Options{
	/** @var string */
	public $econProvider, $statsProvider;
	/** @var string[] */
	public $worlds;
	/** @var string */
	public $database;
	/** @var mixed */
	public $databaseOpts;
	/** @var number */
	public $defaultCash, $defaultBank;
	/** @var int */
	public $founderRank, $defaultRank;
	public $enemyRelation;
	public $defaultRanks = [];
	public $defaultRelations = [];
	/** @var PocketFactions */
	private $plugin;
	public function __construct(PocketFactions $plugin){
		$cfg = $plugin->getConfig();
		FUtils::notNull(null,
			$this->econProvider = $cfg->getNested("dataProviders.economy"),
			$this->statsProvider = $cfg->getNested("dataProviders.statistics"),
			$this->worlds = $cfg->get("factionWorlds", null),
			$this->database = $cfg->getNested("database.name"),
			$this->databaseOpts = $cfg->getNested("database.$this->database"),
			$this->defaultCash = $cfg->getNested("defaultValues.econ.cash"),
			$this->defaultBank = $cfg->getNested("defaultValues.econ.bank"),
			$defaultRanks = $cfg->getNested("defaultValues.ranks"),
			$founderRank = $cfg->getNested("defaultValues.founderRank"),
			$defaultRank = $cfg->getNested("defaultValues.defaultRank"),
			$defaultRelations = $cfg->getNested("defaultValues.relations"),
			$enemyRelation = $cfg->getNested("defaultValues.enemyRelation")
		);
		$ranks = [];
		foreach($defaultRanks as $id => $rank){
			$ranks[$rank["name"]] = $this->defaultRanks[$id]
				= FactionRank::fromConfig($plugin->getLogger(), $id, $rank);
		}
		$relations = [];
		foreach($defaultRelations as $id => $relation){
			$relations[$relation["name"]] = $this->defaultRelations[$id]
				= FactionRelation::fromConfig($plugin->getLogger(), $id, $relation);
		}
		$this->plugin = $plugin;
	}
	public function getDatabase(){
		switch($this->database){
			case "pfdb":
				return new PFDBDatabase($this->plugin, $this->databaseOpts);
		}
		return new DummyDatabase($this->plugin);
	}
}
