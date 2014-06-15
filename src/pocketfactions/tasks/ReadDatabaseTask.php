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
	const WIP       = null;
	public function __construct($res, callable $onFinished, callable $statesSetter, Main $main){
		$this->res = $res;
		$this->onFinished = $onFinished;
		$this->statesSetter = $statesSetter;
		$this->main = $main;
	}
	public function onRun(){
		$res = $this->res;
		$this->setResult(self::WIP);
		$prefix = $this->read($res, strlen(FactionList::MAGIC_P));
		if($prefix !== FactionList::MAGIC_P){
			$this->setResult(self::CORRUPTED);
			return;
		}
		$version = $this->read($res, 1);
		if($version !== Main::V_CURRENT){
			switch($version){
				case Main::V_INIT:
					break;
			}
		}
		$total = Bin::readBin($this->read($res, 4));
		$factions = array();
		for($i = 0; $i < $total; $i++){
			$id = Bin::readBin($this->read($res, 4));
			$name = Bin::readBin($this->read($res, 1));
			$whitelist = false;
			if($name & 0b10000000){
				$whitelist = true;
			}
			$name &= 0b01111111;
			$name = $this->read($res, $name);
			$motto = Bin::readBin($this->read($res, 2));
			$motto = $this->read($res, $motto);
			$founder = Bin::readBin($this->read($res, 1));
			$founder = $this->read($res, $founder);
			$ranks = array();
			for($i = 0; $i < Bin::readBin($this->read($res, 1)); $i++){
				$id = Bin::readBin($this->read($res, 1));
				$rkName = Bin::readBin($this->read($res, 1));
				$rkName = $this->read($res, $rkName);
				$perms = Bin::readBin($this->read($res, 4));
				$ranks[$id] = new Rank($id, $rkName, $perms);
			}
			$defaultRank = Bin::readBin($this->read($res, 1));
			if(!isset($ranks[$defaultRank])){
				trigger_error("Cannot find default rank $defaultRank from resource {$this->res}", E_USER_WARNING);
				$this->setResult(self::CORRUPTED);
				return;
			}
			$defaultRank = $ranks[$defaultRank];
			/**
			 * @var Rank[] $members Ranks indexed by member names, object reference from $ranks (not cloned)
			 */
			$members = array();
			for($i = 0; $i < Bin::readBin($this->read($res, 4)); $i++){
				$mbName = Bin::readBin($this->read($res, 1));
				$mbName = $this->read($res, $mbName);
				$members[$mbName] = $ranks[Bin::readBin($this->read($res, 1))]; // not cloned
			}
			$lastActive = Bin::readBin($this->read($res, 8));
			$chunks = array();
			for($i = 0; $i < Bin::readBin($this->read($res, 2)); $i++){
				$X = Bin::readBin($this->read($res, 4)) - 0x80000000;
				$Z = Bin::readBin($this->read($res, 4)) - 0x80000000;
				$world = Bin::readBin($this->read($res, 1));
				$world = $this->read($res, $world);
				$chunks[] = new Chunk($X, $Z, $world);
			}
			if(count($chunks) == 0){
				$this->setResult(self::CORRUPTED);
				return;
			}
			$baseChunk = array_shift($chunks);
			$home = $this->readPosition($res);
			$factions[$id] = new Faction(array(
				"name" => $name,
				"motto" => $motto,
				"id" => $id,
				"founder" => $founder,
				"ranks" => $ranks,
				"default-rank" => $defaultRank,
				"members" => $members,
				"last-active" => $lastActive,
				"chunks" => $chunks,
				"base-chunk" => $baseChunk,
				"whitelist" => $whitelist,
				"home" => $home,
			), $this->main);
		}
		$states = [];
		for($i = 0; $i < Bin::readBin($this->read($res, 8)); $i++){
			$f0 = Bin::readBin($this->read($res, 4));
			$f1 = Bin::readBin($this->read($res, 4));
			$state = Bin::readBin($this->read($res, 1));
			$states[] = new State($factions[$f0], $factions[$f1], $state);
		}
		if($this->read($res, strlen(FactionList::MAGIC_S)) !== FactionList::MAGIC_S){
			$this->setResult(self::CORRUPTED);
			return;
		}
		if($this->getResult() === self::WIP){
			$this->setResult(self::COMPLETED);
		}
		fclose($res);
		call_user_func($this->statesSetter, $states);
		call_user_func($this->onFinished, $factions, $this);
	}
	protected function read($res, $length = 1){
		$string = fread($res, $length);
		if(!is_string($string) or strlen($string) !== $length){
			$this->setResult(self::CORRUPTED);
			trigger_error("Database corrupted!", E_USER_WARNING);
		}
		return $string;
	}
	protected function readPosition($res){
		$X = Bin::readBin($this->read($res, 4)) - 0x80000000;
		$Z = Bin::readBin($this->read($res, 4)) - 0x80000000;
		$xz = Bin::readBin($this->read($res));
		$z = $xz & 0x0F;
		$x = $xz & 0xF0;
		$x >>= 4;
		$x += ($X * 16);
		$z += ($Z * 16);
		$y = Bin::readBin($this->read($res, 2));
		$world = $this->readString($res);
		$world = $this->forceGetLevel($world);
		return new Position($x, $y, $z, $world);
	}
	protected function forceGetLevel($world){
		$server = Server::getInstance();
		if(!$server->isLevelLoaded($world)){
			if(!$server->isLevelGenerated($world)){
				$server->generateLevel($world, $this->main->getLevelGenerationSeed());
			}
			$server->loadLevel($world);
		}
		return $server->getLevel($world);
	}
	protected function readString($res, $lengthPointer = 1){
		$length = Bin::readBin($this->read($res, $lengthPointer));
		return $this->read($res, $length);
	}
}
