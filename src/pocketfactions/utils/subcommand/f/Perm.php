<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Perm extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "perm");
	}
	public function onRun(array $args, Faction $faction, Player $player){
		// TODO
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_PERM);
	}
	public function getDescription(){
		return "Manage faction internal permissions";
	}
	public function getUsage(){
		return "TODO";
	}
	public function getAliases(){
		return ["p"];
	}
}
