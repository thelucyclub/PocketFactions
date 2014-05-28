<?php

namespace pocketfactions\faction;

class Faction{
	public static $factions;
	private static $main, $path;
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
        * Gets the name of a faction.
        *
        * @return string The name of the faction.
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
