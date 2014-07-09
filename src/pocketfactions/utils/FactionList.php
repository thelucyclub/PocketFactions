<?php

namespace pocketfactions\utils;

use pocketfactions\faction\Rank;
use pocketfactions\faction\State;
use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\Main;
use pocketfactions\tasks\ReadDatabaseTask;
use pocketfactions\tasks\WriteDatabaseTask;
use pocketmine\IPlayer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class FactionList{
	const MAGIC_P = "\x00\x00\xff\xffFACTION-LIST";
	const MAGIC_S = "END-OF-LIST-\xff\xff\x00\x00";
	const WILDERNESS = 0;
	const PVP = 1;
	const SAFE = 2;
	/**
	 * @var bool|IFaction[]
	 */
	private $factions = false;
	/**
	 * @var null|AsyncTask
	 */
	public $currentAsyncTask = null;
	/**
	 * @var State[]
	 */
	private $states = [];
	public function __construct(Main $main){
		$this->path = $main->getFactionsFilePath();
		$this->server = Server::getInstance();
		$this->main = $main;
		$this->load();
	}
	protected function load(){
		if(!is_file($this->path)){
			$this->factions = [];
			$pvp = Faction::newInstance("PvP-Zone", "console", [new Rank(0, "staff", 0)], 0, $this->main, $this->server->getDefaultLevel()->getSafeSpawn(), $this->server->getServerName() . " server-owned PvP areas", true, self::PVP); // console is a banned name in PocketMine-MP
			$this->factions[$pvp->getID()] = $pvp;
			$safe = Faction::newInstance("Safe-Zone", "console", [new Rank(0, "staff", 0)], 0, $this->main, $this->server->getDefaultLevel()->getSafeSpawn(), $this->server->getServerName() . " server-owned PvP-free areas", true, self::SAFE);
			$this->factions[$safe->getID()] = $safe;
		}else{
			$this->loadFrom(fopen($this->path, "rb"));
		}
	}
	/**
	 * @param resource $res
	 */
	public function loadFrom($res){
		$this->scheduleAsyncTask(new ReadDatabaseTask($res, array($this, "setAll"), array($this, "setFactionsStates"), $this->main));
	}
	public function save(){
		$this->saveTo(fopen($this->path, "wb"));
	}
	/**
	 * @param resource $res
	 */
	public function saveTo($res){
		$this->scheduleAsyncTask(new WriteDatabaseTask($res, $this->main));
	}
	/**
	 * @param AsyncTask $asyncTask
	 */
	public function scheduleAsyncTask(AsyncTask $asyncTask){
		if(($this->currentAsyncTask instanceof AsyncTask) and !$this->currentAsyncTask->isFinished()){
			trigger_error("Attempt to schedule an I/O task at Factions database rejected due to another I/O operation at the same resource running");
		}
		$this->server->getScheduler()->scheduleAsyncTask($asyncTask);
	}
	/**
	 * @param IFaction[] $factions
	 */
	public function setAll(array $factions){
		$this->factions = [];
		foreach($factions as $f){
			$this->factions[$f->getID()] = $f;
		}
	}
	public function __destruct(){
		$this->save();
	}
	/**
	 * @return bool|IFaction[]
	 */
	public function getAll(){
		return $this->factions;
	}
	public function getFactionBySimilarName($name){
		$curDelta = PHP_INT_MAX; // with reference to PocketMine-MP, although I can write it myself and I am not even looking at that code now
		$curFact = false;
		foreach($this->factions as $faction){
			if(strpos($faction->getName(), $name) !== false){
				if(strlen($faction->getName()) - strlen($name) < $curDelta){
					$curFact = $faction;
				}
			}
		}
		return $curFact;
	}
	/**
	 * @param string|int|IPlayer|Chunk $identifier
	 * @return bool|null|Faction
	 */
	public function getFaction($identifier){
		if($this->factions === false){
			return null;
		}
		switch(true){
			case is_string($identifier): // faction name
				foreach($this->factions as $faction){
					if(strtolower($faction->getName()) === strtolower($identifier)){
						return $faction;
					}
				}
				return false;
			case is_int($identifier):
				return isset($this->factions[$identifier]) ? $this->factions[$identifier]:false;
			case $identifier instanceof IPlayer:
				foreach($this->factions as $faction){
					if(!($faction instanceof Faction))
						continue;
					if(in_array(strtolower($identifier->getName()), $faction->getMembers())){
						return $faction;
					}
				}
				return false; // should we change this to wilderness?
			case $identifier instanceof Chunk:
				foreach($this->factions as $faction){ // TODO replace the foreach() with a keyed chunks data array to increase optimize performace
					if($faction->hasChunk($identifier)){
						return $faction;
					}
				}
				return false;
			default:
				return false;
		}
	}
	public function getValidFaction($identifier){
		$f = $this->getFaction($identifier);
		return ($f === false ? $this->main->getWilderness():$f);
	}
	public function disband(Faction $faction){
		// TODO remove from list
		// TODO unclaim chunks (required?) (yes if getFaction(Chunk)'s performace is improved)
	}
	/**
	 * @param IFaction $f0
	 * @param IFaction $f1
	 * @return int
	 */
	public function getFactionsState(IFaction $f0, IFaction $f1){
		if($f0 === $f1){
			return State::REL_ALLY;
		}
		if(isset($this->states[$f0->getID() . "-" . $f1->getID()])){
			$this->states[$f0->getID() . "-" . $f1->getID()]->getState();
		}
		if(!($f0 instanceof Faction) or !($f1 instanceof Faction)){ // wilderness
			return State::REL_ALLY;
		}
		return State::REL_NEUTRAL;
	}
	public function setFactionsState(State $state){
		$this->states[$state->getF0()->getID() . "-" . $state->getF1()->getID()] = $state;
	}
	/**
	 * @param State[] $states
	 */
	public function setFactionsStates(array $states){
		foreach($states as $state){
			$this->setFactionsState($state);
		}
	}
	/**
	 * @return State[]
	 */
	public function getFactionsStates(){
		return $this->states;
	}
}
