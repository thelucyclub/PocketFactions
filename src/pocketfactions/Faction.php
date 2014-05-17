<?php

namespace pocketfactions;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\IntArray;
use pocketmine\nbt\tag\String;

class Faction{
	const P_DISBAND = "D";
	const P_SETWHITE = "S";
	const P_CLAIM = "C";
	const P_KICK = "K";
	const P_ADD = "A";
	const P_INVITE = "I";
	const P_UNCLAIM = "U";
	const P_SETPERM = "P";
	const P_BUILD = "B";
	const P_SPENDMONEY = "M";
	protected $nbt;
	public function claimChunk(Chunk $chunk){
		$this->data->Chunks->value[] = $chunk->toRaw();
	}
	public function unclaimChunk(Chunk $chunk){
		unset($this->data->Chunks->value[array_search($chunk->toRaw(), $this->data->Chunks->value)]);
		$this->data->Chunks->value = array_values($this->data->Chunks->value);
	}
	public function hasChunk(Chunk $chunk){
		return in_array($chunk->toRaw(), $this->data->Chunks->value);
	}
	public function addPlayer(Player $player){
		// TODO
	}
	public function __construct($fromDb = false, $arg){
		$this->server = Server::getInstance();
		if($fromDb){
			$this->nbt = new NBT;
			$this->nbt->readCompressed(file_get_contents($arg, LOCK_EX));
			$this->data = $this->nbt->data;
		}
		else{
			$this->data = new Compound;
			$this->data["Name"] = new String;
			$this->data["Founder"] = new String;
			$this->data["Members"] = new String;
			$this->data->Members->setValue($arg["Founder"].":Founder");
			$this->data["Permissions"] = new String;
			$permStr = "";
			$config = Main::get()->getConfig();
			$this->perms = $config->get("default-permissions");
			$this->putPermissionsToDb();
			$this->data["Chunks"] = new IntArray;
			$this->nbtSet();
		}
	}
	protected function nbtSet(Compound $data = null){
		if($data === null)
			$data = $this->data;
		$this->nbt->setData($data);
	}
	public function saveTo($path){
		file_put_contents($path, $this->nbt->writeCompressed(), LOCK_EX);
	}
	public function getName(){
		return $this->name;
	}
	public function __toString(){
		return $this->getName();
	}
}
