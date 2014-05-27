<?php

namespace pocketfactions\faction;

class Faction{
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
}
