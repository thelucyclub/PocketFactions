<?php

namespace pocketfactions\faction;

use pocketfactions\Main;
use pocketmine\Player;
use pocketmine\Server;

class Faction{
	/**
	 * @var string $name
	 */
	protected $name;
	/**
	 * @var string $motto
	 */
	protected $motto;
	/**
	 * @var int $id
	 */
	protected $id;
	/**
	 * @var string $founder
	 */
	protected $founder;
	/**
	 * @var Rank[] $ranks indexed by internal rank IDs
	 */
	protected $ranks;
	/**
	 * @var int $defaultRank internal rank ID of the default rank
	 */
	protected $defaultRank;
	/**
	 * @var Rank[] $members use <code>array_keys()</code> to get a plain list of members
	 */
	protected $members;
	protected $chunks;
	protected $baseChunk;
	protected $home;
	protected $whitelist;
	public $server;
	public function __construct(array $args){
		$this->name = $args["name"];
		$this->motto = $args["motto"];
		$this->id = $args["id"];
		$this->founder = $args["founder"];
		$this->ranks = $args["ranks"];
		$this->defaultRank = $args["default-rank"];
		$this->members = $args["members"];
		$this->chunks = $args["chunks"];
//		$this->chunks = [];
//		/** @var Chunk[] $chunks */
//		$chunks = $args["chunks"];
//		foreach($chunks as $c){
//			if(!isset($this->chunks[$c->getLevel()])){
//				$this->chunks[$c->getLevel()] = [];
//			}
//			$this->chunks[$c->getLevel()][$c->getX().",".$c->getZ()] = $c;
//		}
		$this->baseChunk = $args["base-chunk"];
		$this->whitelist = $args["whitelist"];
		$this->server = Server::getInstance();
		$levels = [];
		foreach($this->chunks as $chunk){
			$level = $chunk->getLevel();
			if(!isset($levels[$level])){
				$levels[] = $level;
				if(!$this->server->isLevelLoaded($level)){
					if(!$this->server->isLevelGenerated($this->server->loadLevel($level))){
						$this->server->generateLevel($level, Main::get()->getUserConfig()->get("seed"));
					}
					$this->server->loadLevel($level);
				}
			}
		}
//		if(Server::getInstance()->isLevelLoaded($args["world"])) {
//	    	$this->world = Server::getInstance()->getLevel($args["world"]);
//		}
//		elseif(Server::getInstance()->isLevelGenerated($args["world"])) {
//			Server::getInstance()->loadLevel($args["world"]);
//			$this->world = Server::getInstance()->getLevel($args["world"]);
//			if(!$this->world instanceof Level) {
//				$this->world = Server::getInstance()->getDefaultLevel();
//			}
//		}
//		$this->home = new Position($args["home"][0], $args["home"][1], $args["home"][2], $this->world);
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return string
	 */
	public function getMotto(){
		return $this->motto;
	}
	public function setMotto($motto){
		$this->motto = $motto;
	}
	/**
	 * @return int
	 */
	public function getID(){
		return $this->id;
	}
	/**
	 * @return string
	 */
	public function getFounder(){
		return $this->founder;
	}
	/**
	 * @return Rank[]
	 */
	public function getRanks(){
		return $this->ranks;
	}
	/**
	 * @return Rank
	 */
	public function getDefaultRank(){
		return $this->defaultRank;
	}
	/**
	 * @return string[] an array of names of members
	 */
	public function getMembers(){
		return array_keys($this->members);
	}
	/**
	 * @return Chunk[]
	 */
	public function getChunks(){
		return $this->chunks;
	}
	/**
	 * @return Chunk
	 */
	public function getBaseChunk(){
		return $this->baseChunk;
	}
	/**
	 * @param string $member
	 * @return Rank
	 */
	public function getMemberRank($member){
		return isset($this->members[strtolower($member)]) ? $this->members[strtolower($member)]:null;
	}
	/**
	 * @return bool
	 */
	public function isWhitelisted(){
		return $this->whitelist;
	}
	/**
	 * @param bool $white
	 */
	public function setWhitelisted($white){
		$this->whitelist = $white;
	}
	/**
	 * @return bool
	 */
	public function isOpen(){
		return !$this->whitelist;
	}
	/**
	 * @param bool $open
	 */
	public function setOpen($open){
		$this->whitelist = !$open;
	}
	/**
	 * @param Player $newMember
	 * @return bool $success
	 */
	public function join(Player $newMember){
		$this->members[strtolower($newMember->getName())] = $this->getDefaultRank();
		return true;
	}
	/**
	 * @param string $memberName
	 */
	public function kick($memberName){

	}
	/**
	 * @param Chunk $chunk
	 */
	public function claim(Chunk $chunk){

	}
	public function hasChunk(Chunk $chunk){

	}
	public function __toString(){
		return $this->getName();
	}
	/**
	 * @return int The next unique faction ID
	 */
	public static function nextID(){
		$fid = Main::get()->getConfig()->get("next-fid");
		Main::get()->getConfig()->set("next-fid", $fid + 1);
		Main::get()->getConfig()->save();
		return $fid;
    }
}
