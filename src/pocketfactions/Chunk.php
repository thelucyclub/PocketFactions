<?php

namespace pocketfactions;

use pocketfactions\io\Buildable;

use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\NamedTag;

class Chunk implements Buildable{
	public $X, $Z;
	public function __construct($X, $Z = null){
		if($Z === null){
			$Z = $X & 0xFF;
			$X = $X >> 16;
			$X &= 0xFF00;
		}
		$this->X = $X;
		$this->Z = $Z;
	}
	public function toRaw(){
		return (($this->X << 16) & 0xFF00) + ($this->Z & 0xFF);
	}
	public static function buildFromSaved($data){
		return new static($data);
	}
}
