<?php

namespace pocketfactions\faction;

use pocketfactions\Main;

class Rank{
	const P_NONE = 0;
	const P_CLAIM         = 0b01;
	const P_UNCLAIM       = 0b10;
	const P_UNCLAIM_ALL  = 0b0110;
	const P_SET_WHITE     = 0b1000;
	const P_ADD_PLAYER   = 0b010000;
	const P_INVITE         = 0b110000;
	const P_BUILD          = 0b01000000;
	const P_BUILD_CENTRE  = 0b11000000;
	const P_KICK_PLAYER   = 0b0100000000;
	const P_ENTER         = 0b01000000000;
	const P_ENTER_CENTRE = 0b10000000000;
	const P_SET_MOTTO     = 0b100000000000;
	const P_TP_HOME        = 0b1000000000000;
	const P_ALL = 0xFFFF;
	public function __construct($id, $name, $perms){
		$this->id = $id;
		$this->name = $name;
		$this->perms = $perms;
	}
	/**
	 * Returns whether the player has permission to $perm. $perm is a constant Rank::P_***
	 * @param int $perm
	 * @return bool
	 */
	public function hasPerm($perm){
		return ($this->perms & $perm) !== 0;
	}
	/**
	 * @return int the ID of the rank in the faction
	 */
	public function getID(){
		return $this->id;
	}
	/**
	 * @return int the sum of all permissions. Check with the bitwise AND operator.
	 */
	public function getPerms(){
		return $this->perm;
	}
	/**
	 * Sets $perm to $bool
	 * @param int $perm
	 * @param bool $bool
	 */
	public function setPerm($perm, $bool){
		if(!$bool){
			$this->perm &= ~$perm;
		}
		else{
			$this->perm |= $perm;
		}
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return static[] the default ranks for a faction
	 */
	public static function defaults(){
		return [
			0 => new static(0, "owner", self::P_ALL),
			1 => new static(1, "member", self::P_ENTER, self::P_ENTER_CENTRE),
			2 => new static(2, "builder", self::P_BUILD),
			3 => new static(3, "core-builder", self::P_BUILD_CENTRE),
			4 => new static(4, "new-member", self::P_ENTER),
		];
		// TODO use config
	}
	/**
	 * @return int default rank internal ID
	 */
	public static function defaultRank(){
		return 4;
	}
	public static function getFactionsInterrank(Faction $f0, Faction $f1){
		$state = Main::get()->getFList()->getFactionsState($f0, $f1);
	}
}
