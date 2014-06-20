<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Home extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "home");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_TP_HOME);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		$player->teleport($faction->getHome());
	}
	public function getDescription(){
		return "Teleport to your faction home";
	}
	public function getUsage(){
		return "";
	}
}
