<?php

namespace pocketfactions\faction;

/**
 * Class Rank
 * This class is about <b>internal</b> ranks. For ranks of other factions, it has not been added yet.
 * @package pocketfactions\faction
 */
class Rank{
	/**
	 * For players: If you are viewing this file to find out the permission names, you found the right line.
	 * Each permission name can be found by a constant name (in this file, const P_<permission> = blahblah;).
	 * The description of each permission is one line above the constant name line.
	 * For example, if you see "The permission to unclaim center chunks (chunks with homes inside)",
	 * you can move on to the next line that says:
	 * const P_UNCLAIM_CENTER = ...;
	 * So the permission name will be UNCLAIM_CENTER.
	 * Note that in commands, you can just type "unclaim_center" or "unclaimcenter" instead of "UNCLAIM_CENTER".
	 */
	/** No permissions at all */
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
	const P_BUILD_CENTER = Rank::P_BUILD_CENTRE;
	/** Collective permission to open all containers */
	const P_OPEN_CONTAINERS =               0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_OPEN_CONTAINER = Rank::P_OPEN_CONTAINERS;
	/** Permission to open (double and single) chests (and deposit/remove items into/from it) */
	const P_OPEN_CHEST =                    0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_OPEN_CHESTS = Rank::P_OPEN_CHEST;
	/** Permission to open furnaces (and deposit/remove items into/from it) */
	const P_OPEN_FURNACE =                  0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_OPEN_FURNACES = Rank::P_OPEN_FURNACE;
	/**
	 * Permission to fight normally.
	 * Must NOT be combined with P_PAIN_FIGHT and P_SOFT_FIGHT. */
	const P_NORM_FIGHT =                 0b0000000000000000000000000000000000000000000000000000000000000000;
	/**
	 * Permission to fight with damage to self too (configured in config.yml).
	 * Must NOT be combined with P_NORM_FIGHT and P_SOFT_FIGHT
	 */
	const P_PAIN_FIGHT =                    0b0000000000000000000000000000000000000000000000000000000000000000;
	/**
	 * Permission to fight with less damage dealt (configured in config.yml).
	 * Must NOT be combined with P_NORM_FIGHT and P_PAIN_FIGHT
	 */
	const P_SOFT_FIGHT =                    0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to deal the last strike that kills a player*/
	const P_KILL =                          0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to fight with less damage received (configured in config.yml) */
	const P_LESS_DAMAGE =                   0b0000000000000000000000000000000000000000000000000000000000000000;
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
	/** Permission to take debt */
	const P_TAKE_DEBT =                     0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to disband the faction */
	const P_DISBAND =                       0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to set faction relations */
	const P_REL_SET =                       0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to teleport to any homes */
	const P_TP_HOME =                       0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to remove/move/create homes */
	const P_SET_HOME =                      0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to rename faction */
	const P_RENAME =                        0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to promote/demote permissions of others who have  */
	const P_PERM =                          0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_PERMS = self::P_PERM;
	const P_PERMISSION = self::P_PERM;
	const P_PERMISSIONS = self::P_PERM;
	/** Permission to set  */
	const P_ALL_PERMS =                     0b0000000000000000000000000000000000000000000000000000000000000000;
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
	/**
	 * @param string $name
	 */
	public function setName($name){
		$this->name = $name;
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
