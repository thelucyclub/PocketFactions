<?php

namespace pocketfactions\faction;

/**
 * This class is about <b>internal</b> ranks. For ranks of other factions, it has not been added yet.
 */
class Rank{
	const P_NONE                        = 0b000000000000000000000000;
	const P_CLAIM                       = 0b000000000000000000000001;
	const P_UNCLAIM                     = 0b000000000000000000000010;
	const P_UNCLAIM_ALL                 = 0b000000000000000000000110;
	const P_BUILD                       = 0b000000000000000000001000;
	const P_BUILD_CENTRE                = 0b000000000000000000011000;
	const P_LAND_RELATED                = 0b000000000000000000011000;
	const P_ENTER                       = 0b000000000000000000100000;
	const P_ENTER_CENTRE                = 0b000000000000000001000000;
	const P_SET_WHITE                   = 0b000000000000000010000000;
	const P_ADD_PLAYER                  = 0b000000000000000100000000; // permission to accept/reject requests from wilderness players to join this faction
	const P_INVITE                      = 0b000000000000001000000000; // permission to send requests to any players to join this faction
	const P_EXT_REQUEST_RELATED         = 0b000000000000001100000000;
	const P_KICK_PLAYER                 = 0b000000000000010000000000;
	const P_MEMBERS_ADMIN               = 0b000000000000011110000000;
	const P_SET_MOTTO                   = 0b000000000000100000000000;
	const P_TP_HOME                     = 0b000000000001000000000000; // teleport to home
	const P_XECON                       = 0b000000011110000000000000;
	const P_SPEND_MONEY_CASH            = 0b000000000010000000000000;
	const P_DEPOSIT_MONEY_CASH          = 0b000000000100000000000000;
	const P_SPEND_MONEY_BANK            = 0b000000001000000000000000;
	const P_DEPOSIT_MONEY_BANK          = 0b000000010000000000000000;
	const P_PERM                        = 0b001111100000000000000000;
	const P_DISBAND = 0b010000000000000000000000;
	const P_CORE = 0b011111100000000000000000;
	const P_ALL = 0b111111111111111111111111; // oops...
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
		return $this->perms;
	}
	/**
	 * Sets $perm to $bool
	 * @param int $perm
	 * @param bool $bool
	 */
	public function setPerm($perm, $bool){
		if(!$bool){
			$this->perms &= ~$perm;
		}
		else{
			$this->perms |= $perm;
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
}
