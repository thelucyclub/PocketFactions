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
	/** Permission to manage permissions */
	const P_PERM =                          0b0000000000000000000000000000000000000000000000000000000000000000;
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
