<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Disband extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "disband");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_DISBAND);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		$this->main->getFList()->disband($faction);
	}
	public function getDescription(){
		return "Disband your faction";
	}
	public function getUsage(){
		return "";
	}
}
