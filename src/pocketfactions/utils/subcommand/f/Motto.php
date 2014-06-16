<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Motto extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "motto");
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args[0])){
			return false;
		}
		$faction->setMotto($args[0]);
	}
	public function getDescription(){
		return "Set your faction's motto";
	}
	public function getUsage(){
		return "<motto>";
	}
	public function hasPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player->getName())->hasPerm(Rank::P_SET_MOTTO);
	}
}
