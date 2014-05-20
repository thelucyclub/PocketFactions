<?php

namespace pocketfactions\faction;

use pocketfactions\io\Buildable;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\NamedTag;

class Chunk{
	public $X, $Z, $level;
	public function __construct($X, $Z){
		$this->X = $X;
		$this->Z = $Z;
		$this->level = $level;
	}
	public function getX(){
		return $this->X;
	}
	public function getZ(){
		return $this->Z;
	}
	public function getLevel(){
		return $this->level;
	}
	public function getWorld(){
		return $this->getLevel();
	}
}
