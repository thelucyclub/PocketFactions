<?php

namespace pocketfactions\faction;

use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\Server;

class Faction{
	public static $factions;
	private static $main, $path;
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
		if(Server::getInstance()->isLevelLoaded($args["world"])) {
	    	$this->world = Server::getInstance()->getLevel($args["world"]);
		}elseif(Server::getInstance()->isLevelGenerated($args["world"])) {
			Server::getInstance()->loadLevel($args["world"]);
			$this->world = Server::getInstance()->getLevel($args["world"]);
			if(!$this->world instanceof Level) {
				$this->world = Server::getInstance()->getDefaultLevel();
			}
		}
		$this->home = new Position($args["home"][0], $args["home"][1], $args["home"][2], $this->world);
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
    *
    * Gets the motto for this faction.
    *  
    * @return string The motto for this faction.
    *
    */
    public function getMotto() {
        return $this->motto;
    }
  
   /**
    *
    * Sets the motto for this faction.
    *
    */
    public function setMotto(string motto) {
        $this->motto = $motto;
    }
    
   /**
    *
    * Gets the home point for this faction.
    *  
    * @return Position The home for this faction.
    *
    */
    public function getHome() {
        return $this->home;
    }
  
   /**
    *
    * Sets the home point for this faction.
    *
    */
    public function setHome(Position $pos) {
        $pos->level = $this->world;
        $this->home = $pos;
    }

    /**
     * 
     * Gets the ID of a faction.
     *
     * @return int The ID of the faction.
     *
     */   
	public function getID(){
		return $this->id;
	}

    /**
     * Gets the Player object for the 
     * person who started the faction.
     *
     * @return Player The founder of this faction.
     *
      */	
	public function getFounder(){
		return $this->founder;
	}
	
       /**
        * Gets all ranks into an array for this faction. 
        *
        * @return Rank[] An array of ranks.
        *
        */
        
	public function getRanks(){
		return $this->ranks;
	}
	
       /**
        * Gets the default rank for this faction.
        *
        * @return Rank The default of the faction.
        *
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
	    return $this->members:
	}
      
       /**
        * Gets all chunks claimed by this faction.
        *
        * @return Chunk[] The default of the faction.
        *
        */	
	public function getChunks(){
		return $this->chunks;
	}
	
       /**
        * Get the base chunk for this faction.
        *
        * @return Chunk The base chunk.
        *
        */	
	public function getBaseChunk(){
            return $this->baseChunk;
	}

	public static function addFaction($args){
	    if($this->factions === false){
			return false;
		}
		$this->factions[] = new Faction($this->nextID());
	}
    
    public static function rmFaction($args){
        //remove a faction
    }
   
    public static function usrFaction($name){
        //get user info
    }
   
    public static function usrFactionPerm($name){
        //get user rank
    }
    
    public static function invFaction(Player $p){
        //invite target player in a faction
    }
   
    public static function existFaction($name){
        //checks if faction exist or not
    }
   
    public static function joinFaction($args){
        //join a faction
    }
    
    public static function nextID(){
	$fid = $this->main->getConfig()->get("next-fid");
	self::main->getConfig()->set("next-fid", $fid + 1);
	self::main->getConfig()->save();
	return $fid;
    }
	
    public static function load(){
	$res = fopen($this->path, "rb");
        self::loadFrom($res);
    }
    
    public static function loadFrom($res){
	Server::getInstance()->getScheduler()->scheduleAsyncTask(new ReadDatabaseTask($res), array($this, "onLoaded"));
    }
    
    public static function onLoaded(array $factions){
	self::factions = $factions;
    }
    
    public static function save(){
	$res = fopen($this->path, "wb");
	self::saveTo($res);
    }
	
    public static function saveTo($res){
	Server::getInstance()->getScheduler()->scheduleAsyncTask(new WriteDatabaseTask($res));
    }
	
}
