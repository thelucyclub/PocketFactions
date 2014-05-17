<?php

namespace pocketfactions\io;

use pocketfactions\Faction;

use pocketmine\Server;

class Database extends PluginTask{
	const S0_INTERVAL = 1200;
	const S0_UPDATE_INDEX = 400;
	public $factions = array();
	protected $flChanged = false;
	protected $path;
	public function __construct($path){
		parent::__construct(Main::get());
		if(!is_dir($path)){
			mkdir($path);
			file_put_contents($path."index.json", json_encode(array()));
		}
		else{
			foreach(json_decode(file_get_contents("index.json")) as $faction){
				$f = new Faction(true, $path."$faction.dat");
				$this->factions[$f->getName()] = $f;
			}
		}
		$this->path = $path;
		Server::getInstance()->getScheduler()->scheduleDelayedRepeatingTask($this, self::S0_UPDATE_INDEX, self::S0_INTERVAL);
	}
	public function addFaction(Faction $faction){
		$this->factions[$faction->getName()] = $faction;
		$faction->saveTo($this->path."$faction.dat");
	}
	public function rmFaction(Faction $faction){
		unset($this->factions[$faction->getName()]);
		$faction->finalize();
		unlink($this->path."$faction.dat");
	}
	public function onRun($ticks){
		if($this->flChanged){
			file_put_contents($this->path."index.json", json_encode($this->factions));
			$this->flChanged = false;
		}
	}
	public function getPath(){
		return $this->path;
	}
}
