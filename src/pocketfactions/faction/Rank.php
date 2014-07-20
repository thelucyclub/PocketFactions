<?php

namespace pocketfactions\faction;
/**
 * Class Rank
 * This class is about <b>internal</b> ranks. For ranks of other factions, it has not been added yet.
 * @package pocketfactions\faction
 */
class Rank{
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
	/** Permission to enter normal chunks */
	const P_ENTER =                         0b0000000000000000000000000000000000000000000000000000000000000000;
	/** Permission to enter center chunks */
	const P_ENTER_CENTRE =                  0b0000000000000000000000000000000000000000000000000000000000000000;
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
	const P_XECON =                         0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_SPEND_MONEY_CASH =              0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_DEPOSIT_MONEY_CASH =            0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_SPEND_MONEY_BANK =              0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_SPEND_MONEY_BANK_OVERDRAFT =    0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_DEPOSIT_MONEY_BANK =            0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_PERM =                          0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_DISBAND =                       0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_TP_HOME =                       0b0000000000000000000000000000000000000000000000000000000000000000; // teleport to home
	const P_SET_HOME =                      0b0000000000000000000000000000000000000000000000000000000000000000; // set home position
	const P_RENAME =                        0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_CORE =                          0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_CHAT_ALL =                      0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_CHAT_ADMIN =                    0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_CHAT_ANNOUNCEMENT =             0b0000000000000000000000000000000000000000000000000000000000000000;
	const P_ALL =                           0b1111111111111111111111111111111111111111111111111111111111111111;
	// TODO Change these into actual values
	// WARNING there can be a maximum of 64 permissions. We already have 32 here, half of them used up. (I started expecting 8 and ended up 64)
	public function __construct($id, $name, $perms){
		$this->id = $id;
		$this->name = $name;
		$this->perms = $perms;
	}
	/**
	 * Returns whether the player has permission to $perm. $perm is a constant Rank::P_***
	 * @param int $perm
	 * @param bool $isOr
	 * @return bool
	 */
	public function hasPerm($perm, $isOr = true){
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
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return self[] the default ranks for a faction
	 */
	public static function defaults(){

		return [];
	}
	/**
	 * @return int default rank internal ID
	 */
	public static function defaultRank(){
		return 4;
	}
}
