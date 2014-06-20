<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Quit extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "quit");
	}
	public function checkPermission(Faction $faction, Player $player){
		return true;
	}
	public function onRun(array $args, Faction $faction, Player $player){
		$members = $faction->getMembers();
		unset($members[strtolower($player->getName())]);
		$faction->setMembers($members);
		$faction->sendMessage($player->getName() . " left the faction!");
		return "You have successfully left $faction.";
	}
	public function getDescription(){
		return "Leave your current faction";
	}
	public function getUsage(){
		return "";
	}
}
