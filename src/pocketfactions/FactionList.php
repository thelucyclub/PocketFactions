<?php

namespace pocketfactions;

use pocketfactions\tasks\ReadDatabaseTask;
use pocketfactions\tasks\WriteDatabaseTask;

use pocketmine\utils\Binary as Bin;

class FactionList{
	const MAGIC_P = "\0xfa\0xc7\0x10\0x25"; // leet for factions
	const MAGIC_S = "\0xde\0xad\0xc0\0xde"; // leet for deadcode
	public $factions = array();
	public function __construct($path){
		$this->path = $path;
		$this->load();
	}
	public function __destruct(){
		$this->save();
	}
	public function load(){
		$res = fopen($this->path, "r");
		$this->loadFrom($res);
	}
	public function loadFrom($res){
		Server::getInstance()->getScheduler()->scheduleAsyncTask(new ReadDatabaseTask($res));
	}
	public function save(){
		$res = fopen($this->path, "w");
		$this->saveTo($res);
	}
	public function saveTo($res){
		Server::getInstance()->getScheduler()->scheduleAsyncTask(new WriteDatabaseTask($res));
	}
}
