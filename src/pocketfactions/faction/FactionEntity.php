<?php

/**
 * IMPORTANT Due to SPL Class Auto Loader, we cannot use if(class_exists()){} to block a class statement inside.
 * Therefore, CHECK IF xEcon IS LOADED EVERY TIME when one of these actions (that causes SPL to load this class) are going to be done:
 * <ul>
 *   <li>You create a new instance of FactionEntity</li>
 *   <li>You call a function that has a type hint of this class (prevent adding this kind of functions or parameter type hints)</li>
 * </ul>
 */

namespace pocketfactions\faction;

use xecon\entity\Entity;

class FactionEntity extends Entity{
	protected $faction;
	public function __construct(Faction $faction){
		$this->faction = $faction;
	}
	public function getName(){
		return "FactionEntity";
	}
	public function getAbsolutePrefix(){
		return "PCFT_FACTION_ENT";
	}
	public function getClass(){
		return "pocketfactions\\faction\\FactionEntity";
	}
	public function initDefaultAccounts(){
		$this->addAccount("Cash", 400);
	}
}
