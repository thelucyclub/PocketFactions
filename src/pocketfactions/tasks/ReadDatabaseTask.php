<?php

namespace pocketfactions\tasks;

use pocketfactions\faction\State;
use pocketfactions\utils\FactionList;
use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketmine\level\Position;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class ReadDatabaseTask extends AsyncTask{
	const CORRUPTED = 1;
	const COMPLETED = null;
	const WIP = null;
	protected $buffer = "";
	protected $offset = 0;
	public function __construct($res, callable $onFinished, callable $statesSetter, Main $main, $isAsync = true){
		$this->res = $res;
		$this->onFinished = $onFinished;
		$this->statesSetter = $statesSetter;
		$this->main = $main;
		if(!$isAsync){
			$this->onRun();
			$this->onCompletion($main->getServer());
		}
	}
	public function onRun(){
		if(!$this->buffer){
			$this->buffer = stream_get_contents($this->res);
			fclose($this->res);
		}
	}
	public function onCompletion(Server $server){
		$this->setResult(self::WIP);
		$prefix = $this->read(strlen(FactionList::MAGIC_P));
		if($prefix !== FactionList::MAGIC_P){
			$this->setResult(self::CORRUPTED);
			return;
		}
		$version = $this->read(1);
		if($version !== Main::V_CURRENT){
			switch($version){
				case Main::V_INIT:
					break;
			}
		}
		$total = Bin::readBin($this->read(4));
		$factions = array();
		for($i = 0; $i < $total; $i++){
			$id = Bin::readBin($this->read(4));
			$name = Bin::readBin($this->read(1));
			$whitelist = false;
			if($name & 0b10000000){
				$whitelist = true;
			}
			$name &= 0b01111111;
			$name = $this->read($name);
			$motto = Bin::readBin($this->read(2));
			$motto = $this->read($motto);
			$founder = Bin::readBin($this->read(1));
			$founder = $this->read($founder);
			$ranks = array();
			for($i = 0; $i < Bin::readBin($this->read(1)); $i++){
				$id = Bin::readBin($this->read(1));
				$rkName = Bin::readBin($this->read(1));
				$rkName = $this->read($rkName);
				$perms = Bin::readBin($this->read(8));
				$ranks[$id] = new Rank($id, $rkName, $perms);
			}
			$defaultRank = Bin::readBin($this->read(1));
			$allyRank = Bin::readBin($this->read(1));
			$truceRank = Bin::readBin($this->read(1));
			$stdRank = Bin::readBin($this->read(1));
			/**
			 * @var int[] $members Rank IDs indexed by member names
			 */
			$members = array();
			for($i = 0; $i < Bin::readBin($this->read(4)); $i++){
				$mbName = Bin::readBin($this->read(1));
				$mbName = $this->read($mbName);
				$members[$mbName] = Bin::readBin($this->read(1));
			}
			$lastActive = Bin::readBin($this->read(8));
			$reputation = Bin::readBin($this->read(8)) - 0x8000000000000000;
			$chunks = array();
			for($i = 0; $i < Bin::readBin($this->read(2)); $i++){
				$X = Bin::readBin($this->read(4)) - 0x80000000;
				$Z = Bin::readBin($this->read(4)) - 0x80000000;
				$world = Bin::readBin($this->read(1));
				$world = $this->read($world);
				$chunks[] = new Chunk($X, $Z, $world);
			}
			$homes = [];
			for($i = 0; $i < $this->read(1); $i++){
				$homeName = $this->readString();
				$homes[$homeName] = $this->readPosition();
			}
			$factions[$id] = new Faction([
				"name" => $name,
				"motto" => $motto,
				"id" => $id,
				"founder" => $founder,
				"ranks" => $ranks,
				"default-rank" => $defaultRank,
				"ally-rank" => $allyRank,
				"truce-rank" => $truceRank,
				"std-rank" => $stdRank,
				"members" => $members,
				"last-active" => $lastActive,
				"chunks" => $chunks,
				"whitelist" => $whitelist,
				"homes" => $homes,
				"reputation" => $reputation,
			], $this->main);
		}
		$states = [];
		for($i = 0; $i < Bin::readBin($this->read(8)); $i++){
			$f0 = Bin::readBin($this->read(4));
			$f1 = Bin::readBin($this->read(4));
			$state = Bin::readBin($this->read(1));
			$states[] = new State($factions[$f0], $factions[$f1], $state);
		}
		if($this->read(strlen(FactionList::MAGIC_S)) !== FactionList::MAGIC_S){
			$this->setResult(self::CORRUPTED);
			return;
		}
		if($this->getResult() === self::WIP){
			$this->setResult(self::COMPLETED);
		}
		call_user_func($this->statesSetter, $states);
		call_user_func($this->onFinished, $factions, $this);
		$this->main->getLogger()->info("PocketFactions database parsing completed.");
	}
	/**
	 * @param int $length
	 * @return string
	 */
	protected function read($length){
		$string = substr($this->buffer, $this->offset, $length);
		$this->offset += $length;
		if(strlen($string) < $length){
			trigger_error("PocketFactions database corrupted: Unexpected end of file", E_USER_WARNING);
		}
		return $string;
	}
	protected function readPosition(){
		$X = Bin::readBin($this->read(4)) - 0x80000000;
		$Z = Bin::readBin($this->read(4)) - 0x80000000;
		$xz = Bin::readBin($this->read(1));
		$z = $xz & 0x0F;
		$x = $xz & 0xF0;
		$x >>= 4;
		$x += ($X * 16);
		$z += ($Z * 16);
		$y = Bin::readBin($this->read(2));
		$world = $this->readString(1);
		$world = $this->forceGetLevel($world);
		return new Position($x, $y, $z, $world);
	}
	protected function forceGetLevel($world){
		$server = $this->main->getServer();
		if(!$server->isLevelLoaded($world)){
			if(!$server->isLevelGenerated($world)){
				$server->generateLevel($world, $this->main->getLevelGenerationSeed());
			}
			$server->loadLevel($world);
		}
		return $server->getLevel($world);
	}
	protected function readString($lengthPointer = 1){
		$length = Bin::readBin($this->read($lengthPointer));
		return $this->read($length);
	}
}
