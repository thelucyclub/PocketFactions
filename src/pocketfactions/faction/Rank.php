<?php

namespace pocketfactions\faction;
use pocketfactions\Main;

/**
 * Class Rank
 * This class is about <b>internal</b> ranks. For ranks of other factions, it has not been added yet.
 * @package pocketfactions\faction
 */
class Rank{
	// if permissions are to be made impossible to modify, leave 0 here.
	/**
	 * No permissions at all
	 */
	const P_NONE =                          0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to claim chunks by normal means */
	const P_CLAIM =                         0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to claim chunks by sieging */
	const P_SIEGE =                         0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to unclaim normal chunks */
	const P_UNCLAIM =                       0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to unclaim center chunks (chunks with homes inside) */
	const P_UNCLAIM_CENTER =                0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to unclaim all chunks in once */
	const P_UNCLAIM_ALL =                   0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to build in normal chunks */
	const P_BUILD =                         0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to build in center chunks (chunks with homes inside) */
	const P_BUILD_CENTRE =                  0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Collective permission to open all containers */
	const P_OPEN_CONTAINERS =               0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to open (double and single) chests (and deposit/remove items into/from it) */
	const P_OPEN_CHEST =                    0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to open furnaces (and deposit/remove items into/from it) */
	const P_OPEN_FURNACE =                  0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to fight in claimed chunks with less damage received */
	const P_NORM_FIGHT =                    0b0000000000000000000000000000000000000000000000000000000000000000;
//	/** Permission to enter normal chunks */
//	const P_ENTER =                         0b0000000000000000000000000000000000000000000000000000000000000000;
//	/** Permission to enter center chunks */
//	const P_ENTER_CENTRE =                  0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to set the faction to whitelist or open */
	const P_SET_WHITE =                     0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to accept a request to join this faction */
	const P_ADD_PLAYER =                    0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to send an invitation to join this faction */
	const P_INVITE =                        0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to kick a player out of the faction */
	const P_KICK_PLAYER =                   0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to set the faction's motto */
	const P_SET_MOTTO =                     0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Collective permission to spend faction money */
	const P_SPEND_MONEY =                   0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Collective permission to deposit money into faction accounts */
	const P_DEPOSIT_MONEY =                 0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to decrease cash amount */
	const P_SPEND_MONEY_CASH =              0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to increase cash amount */
	const P_DEPOSIT_MONEY_CASH =            0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to decrease bank amount without overdraft */
	const P_SPEND_MONEY_BANK =              0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to decrease bank amount until no more bank money could be withdrawn. */
	const P_SPEND_MONEY_BANK_OVERDRAFT =    0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to increase bank amount */
	const P_DEPOSIT_MONEY_BANK =            0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to disband the faction */
	const P_DISBAND =                       0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to teleport to any homes */
	const P_TP_HOME =                       0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to remove/move/create homes */
	const P_SET_HOME =                      0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to rename faction */
	const P_RENAME =                        0b0000000000000000000000000000000000000000000000000000000000000000;
	/**
	 * Lowest level of broadcast
	 * EVERYONE should be able to listen to it
	 */
	const P_CHAT_ALL =                      0b0000000000000000000000000000000000000000000000000000000000000000;
	/**
	 * Administrative level broadcast
	 * Request- and xEcon-related messages sent to the faction are default to be sent at this level.
	 * Administration-related broadcasts are sent here too.
	 */
	const P_CHAT_ADMIN =                    0b0000000000000000000000000000000000000000000000000000000000000000;
	/**
	 * Announcement level broadcast
	 * Announcement to all faction members.
	 */
	const P_CHAT_ANNOUNCEMENT =             0b0000000000000000000000000000000000000000000000000000000000000000;
	/** All permissions that can be granted to a faction member */
	const P_ALL =                           0b1111111111111111111111111111111111111111111111111111111111111111;
	// TODO Change these into actual values
	protected $id;
	protected $name;
	protected $perms;
	protected $description;
	public function __construct($id, $name, $perms, $description = ""){
		$this->id = $id;
		$this->name = $name;
		$this->perms = $perms;
		$this->description = $description;
	}
	/**
	 * Returns whether the player has permission to $perm. $perm is a constant Rank::P_***
	 * @param int $perm
	 * @param bool $isOr
	 * @return bool
	 */
	public function hasPerm($perm, $isOr = true){
		if($perm === 0){
			return true;
		}
		$and = $perm & $this->perms;
		return $isOr ? $and !== 0:$and === $perm;
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
		}else{
			$this->perms |= $perm;
		}
	}
	public function setPermsRaw($perms){
		$this->perms = $perms;
	}
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	public function __toString(){
		return $this->name;
	}
	/**
	 * @param Main $main
	 * @return self[] the default ranks for a faction
	 */
	public static function defaults(Main $main){
		/** @var Rank[] $out */
		$out = [];
		$rels = [];
		foreach($main->getConfig()->get("default ranks") as $rank){
			$id = $rank["id"];
			if(isset($out[$id])){
				$main->getLogger()->warning("Default rank ID $id is duplicated! ".
					"Only the first one will be used.");
				continue;
			}
			$perms = 0;
			if(isset($ranks["permissions"])){
				foreach($rank["permissions"] as $origPerm){
					$perm = $origPerm;
					$inverse = false;
					if(substr($perm, 0, 1) === "!"){
						$perm = substr($perm, 1);
						$inverse = true;
					}
					if(defined($path = get_class()."::$perm")){
						if($inverse){
							$perms &= ~constant($path);
						}
						else{
							$perms |= constant($path);
						}
					}
					else{
						$main->getLogger()->warning("Undefined permission node: $perm. This permission will be ignored.");
					}
				}
			}
			$out[$id] = new Rank($id, $rank["name"], $perms, isset($rank["description"]) ? $rank["description"]:"");
			if(isset($rank["parent"])){
				if($rank["parent"] >= $id){
					$main->getLogger()->error("Parent rank ID must be smaller than child rank ID! ".
						"(Rank ID $id < ".$rank["parent"].".) Some bugs might occur if you don't stop ".
						"the server and fix it.");
				}
				$rels[$id] = $rank["parent"];
			}
		}
		ksort($rels, SORT_NUMERIC); // no more recursiveness :)
		foreach($rels as $child => $parent){
			$out[$child]->setPermsRaw($out[$child]->getPerms() | $out[$parent]->getPerms());
		}
		return $out;
	}
	public static function defaultRank(Main $main){
		return $main->getConfig()->get("default rank");
	}
	public static function defaultAllyRank(Main $main){
		return $main->getConfig()->get("ally rank");
	}
	public static function defaultTruceRank(Main $main){
		return $main->getConfig()->get("truce rank");
	}
	/**
	 * @return string
	 */
	public function getDescription(){
		return $this->description;
	}
	/**
	 * @param string $description
	 */
	public function setDescription($description){
		$this->description = $description;
	}
}
