<?php

namespace pocketfactions\faction;

use pocketfactions\Main;
use pocketmine\Player;

class Faction{
	public static $factions;
	protected $name;
	protected $id;
	protected $founder;
	protected $ranks;
	protected $defaultRank;
	protected $members;
	protected $chunks;
	protected $baseChunk;
	public function __construct(array $args){
		$this->name = $args["name"];
		$this->id = $args["id"];
		$this->founder = $args["founder"];
		$this->ranks = $args["ranks"];
		$this->defaultRank = $args["default-rank"];
		$this->members = $args["members"];
		$this->chunks = $args["chunks"];
		$this->baseChunk = $args["base-chunk"];
	}
	
       /**
        * 
        * Gets the name of a faction.
        *
        * @return string The name of the faction.
        *
        */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return int The ID of the faction
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
	 * @return int A player's default rank when he joins the faction
	 */
	public function getDefaultRank(){
		return $this->defaultRank;
	}
    /**
        * Gets an array of all members in this faction.
        *
        * @return Player[] An array of faction members.
        *
        */
	public function getMembers(){
	    return $this->members;
	}
	/**
	 * @return Chunk[] All claimed chunks of the faction
	 */
	public function getChunks(){
		return $this->chunks;
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
