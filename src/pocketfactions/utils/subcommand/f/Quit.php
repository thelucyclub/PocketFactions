<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\Main;
use pocketmine\Player;

class Quit extends FactionMemberSubcommand{
	public function __construct(Main $main){
		parent::__construct($main, "quit");
	}
	public function checkPermission(Faction $faction, Player $player){
		return true;
	}
	public function onRun(array $args, Faction $faction, Player $player){
		$reason = implode(" ", $args);
		$members = $faction->getMembers(true);
		unset($members[strtolower($player->getName())]);
		$faction->setMembers($members);
		$faction->sendMessage($player->getName() . " quitted the faction.", Faction::CHAT_ANNOUNCEMENT);
		if(strlen($reason) > 0){
			$faction->sendMessage("Reason: $reason");
		}
		return "[PF] You have successfully quitted $faction.";
	}
	public function getDescription(){
		return "Leave your current faction";
	}
	public function getUsage(){
		return "";
	}
}
