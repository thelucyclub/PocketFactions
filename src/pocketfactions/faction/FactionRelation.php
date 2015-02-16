<?php

namespace pocketfactions\faction;

use pocketfactions\PocketFactions;

class FactionRelation{
	/** @var int */
	private $id;
	/** @var string */
	private $name;
	/** @var int */
	public $bitmask0, $bitmask1;
	/**
	 * @param int $id
	 * @param string $name
	 * @param int $bitmask0
	 * @param int $bitmask1
	 */
	public function __construct($id, $name, $bitmask0, $bitmask1){
		$this->id = $id;
		$this->name = $name;
		$this->bitmask0 = $bitmask0;
		$this->bitmask1 = $bitmask1;
	}
	public static function getDefaultRelations(PocketFactions $plugin){
		return $plugin->getOpts()->defaultRelations;
	}
	public static function fromConfig(\Logger $logger, $id, $relation){
		$bitmask0 = 0;
		$bitmask1 = 0;
		foreach($relation["permissions"] as $perm){
			$bit = FactionPermission::fromString($perm, $tier);
			if($tier === -1){
				$logger->warning("Unknown permission \"$perm\" will be ignored.");
				continue;
			}
			if($tier === 0){
				$bitmask0 |= $bit;
			}
			else{
				$bitmask1 |= $bit;
			}
		}
		foreach($relation["excluded permissions"] as $perm){
			$bit = FactionPermission::fromString($perm, $tier);
			if($tier === -1){
				$logger->warning("Unknown permission \"$perm\" will be ignored.");
				continue;
			}
			if($tier === 0){
				$bitmask0 &= ~$bit;
			}
			else{
				$bitmask1 &= ~$bit;
			}
		}
		return new self($id, $relation["name"], $bitmask0, $bitmask1);
	}
	/**
	 * @return int
	 */
	public function getId(){
		return $this->id;
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @param string $name
	 */
	public function setName($name){
		$this->name = $name;
	}
	/**
	 * @param FactionPermission $perm
	 */
	public function addPermission(FactionPermission $perm){
		if($perm->tier === 0){
			$this->bitmask0 |= $perm->bitmask;
		}else{
			$this->bitmask1 |= $perm->bitmask;
		}
	}
	/**
	 * @param FactionPermission $perm
	 */
	public function revokePermission(FactionPermission $perm){
		if($perm->tier === 0){
			$this->bitmask0 &= ~$perm->bitmask;
		}
		else{
			$this->bitmask1 &= ~$perm->bitmask;
		}
	}
}
