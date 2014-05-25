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
	}
	public function getName(){
		return $this->name;
	}
	public function getID(){
		return $this->id;
	public function getFounder(){
		return $this->founder;
	}
	public function getRanks(){
		return $this->ranks;
	}
	public function getDefaultRank(){
		return $this->defaultRank;
	}
	public function getMembers(){
		return $this->members:
	}
	public function getChunks(){
		return $this->chunks;
	}
	public function getBaseChunk(){
		return $this->baseChunk;
	}
}
