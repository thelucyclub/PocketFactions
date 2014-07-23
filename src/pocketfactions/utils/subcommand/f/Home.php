<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\level\Position;
use pocketmine\Player;

class Home extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "home");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $this->getMain()->getMaxHomes() > 0 and $faction->getMemberRank($player)->hasPerm(Rank::P_TP_HOME);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		// TODO find the correct named home
		if(isset($args[0])){
			$home = $faction->getHome($args[0]);
			if($home instanceof Position){
				$player->teleport($home);
				return "You have been teleported to $args[0].";
			}
			return "There is no such home named \"$args[0]\"!";
		}
		$home = $faction->getHome();
		if($home instanceof Position){
			$player->teleport($home);
			return "You have been teleported to your faction home.";
		}
		return "Your faction doesn't seem to have a home.";
	}
	public function getDescription(){
		return "Teleport to your faction home";
	}
	public function getUsage(){
		return $this->getMain()->getMaxHomes() > 1 ? "[name] (if no name is provided, you will be\n    teleported to the earliest existing faction home":""; // shoghicp hates dynamic usages, but this is a subcommand map, so it doesn't match his "make plugin uasges static" statement :P
	}
	public function getAliases(){
		return ["h"];
	}
}
