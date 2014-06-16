<?php
/**
 * Created by PhpStorm.
 * User: 15INCH
 * Date: 14年6月16日
 * Time: 下午4:21
 */
namespace pocketfactions\utils;

use pocketfactions\faction\Chunk;
use pocketfactions\Main;
use pocketmine\entity\Entity;

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
	public function canFight(Entity $a, Entity $v){
		return true;
	}
	public function getMain(){
		return $this->main;
	}
}
