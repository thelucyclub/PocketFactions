<?php

namespace pocketfactions\utils;

use pocketfactions\faction\Chunk;
use pocketfactions\Main;
use pocketmine\level\Position;
use pocketmine\Player;

class WildernessFaction implements IFaction{
	public function __construct(Main $main){
		$this->main = $main;
	}
	public function getID(){
		return 0;
	}
	public function getName(){
		return "Wilderness";
	}
	public function getDisplayName(){
		return "~~Wilderness~~";
	}
	public function isOpen(){
		return false;
	}
	public function getMemberRank($m){
		return false;
	}
	public function hasMember($m){
		return false;
	}
	public function hasChunk(Chunk $chunk){
		return true;
	}
	public function getMain(){
		return $this->main;
	}
	public function canBuild(Player $player, Position $pos){
		return true;
	}
}
