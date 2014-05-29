<?php

namespace pocketfactions\faction;

use pocketfactions\Main;
use pocketmine\Player;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\Server;

class Faction{
	public static $factions;
	protected $name;
	protected $motto;
	protected $id;
	protected $founder;
	protected $ranks;
	protected $defaultRank;
	protected $members;
	protected $chunks;
	protected $baseChunk;
	protected $home;
	protected $whitelist;
	public function __construct(array $args){
		$this->name = $args["name"];
		$this->motto = $args["motto"];
		$this->id = $args["id"];
		$this->founder = $args["founder"];
		$this->ranks = $args["ranks"];
		$this->defaultRank = $args["default-rank"];
		$this->members = $args["members"];
		$this->chunks = $args["chunks"];
		$this->baseChunk = $args["base-chunk"];
		$this->whitelist = $args["whitelist"];
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
	 * @param string
	 * @return Rank
	 */
	public function getMemberRank($member){
		return $this->members[strtolower($member)];
	}
	/**
	 * @return bool
	 */
	public function isWhitelisted(){
		return $this->whitelist;
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
