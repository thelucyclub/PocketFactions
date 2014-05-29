<?php

namespace pocketfactions;

use pocketfactions\faction\Faction;
use pocketfactions\tasks\ReadDatabaseTask;
use pocketfactions\tasks\WriteDatabaseTask;
use pocketmine\Player;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class FactionList{
	/**
	 * @var bool|Faction[]
	 */
	public $factions = false;
	/**
	 * @var null|AsyncTask
	 */
	public $currentAsyncTask = null;
	public function __construct(){
		$this->path = Main::get()->getFactionsFilePath();
		$this->server = Server::getInstance();
		$this->load();
	}
	protected function load(){
		$this->loadFrom(fopen($this->path, "rb"));
	}
	/**
	 * @param resource $res
	 */
	public function loadFrom($res){
		$this->scheduleAsyncTask(new ReadDatabaseTask($res, array($this, "setAll")));
	}
	public function save(){
		$this->saveTo(fopen($this->path, "wb"));
	}
	/**
	 * @param resource $res
	 */
	public function saveTo($res){
		$this->scheduleAsyncTask(new WriteDatabaseTask($res));
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
	 * @param Faction[] $factions
	 */
	public function setAll(array $factions){
		$this->factions = $factions;
	}
	public function __destruct(){
		$this->save();
	}
	public function getFaction($identifier){
		if($this->factions === false){
			return null;
		}
		switch(true){
			case is_string($identifier): // faction name
				foreach($this->factions as $faction){
					if($faction->getName() === $identifier){
						return $faction;
					}
				}
				return false;
			case is_int($identifier):
				return isset($this->factions[$identifier]) ? $this->factions[$identifier]:false;
			case $identifier instanceof Player:
				foreach($this->factions as $faction){
					if(in_array(strtolower($identifier->getName()), $faction->getMembers())){
						return $faction;
					}
				}
				return false;
			default:
				return false;
		}
	}
	public function addFaction(array $args, $id){
		$this->factions[$id] = new Faction($args);
	}
}
