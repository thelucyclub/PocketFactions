<?php

namespace pocketfactions\faction;

use pocketfactions\PocketFactions;

class FactionBuilder{
	/** @var PocketFactions */
	private $plugin;
	/** @var string */
	private $name;
	/** @var string */
	private $founder;
	/** @var string */
	private $motto;
	/** @var FactionRank[] */
	private $ranks;
	private $relations;
	public static function getInstance(PocketFactions $plugin){
		return new self($plugin);
	}
	private function __construct(PocketFactions $plugin){
		$this->plugin = $plugin;
		$this->ranks = FactionRank::getDefaultRanks($plugin);
		$this->relations = FactionRelation::getDefaultRelations($plugin);
	}
	/**
	 * @param string $name
	 */
	public function setName($name){
		$this->name = $name;
	}
	/**
	 * @param string $name
	 */
	public function setFounder($name){
		$this->founder = $name;
	}
	/**
	 * @param string $motto
	 */
	public function setMotto($motto){
		$this->motto = $motto;
	}
	/**
	 * @param int|null $id
	 * @return Faction
	 */
	public function build($id = null){
		return new Faction($this, $id === null ? $this->plugin->getDatabase()->nextId():$id);
	}
}
