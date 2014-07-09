<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Setopen extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "setopen");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_SET_WHITE);
	}
	public function onRun(array $args, Faction $faction){
		if(!isset($args[0])){
			$args[0] = "check";
		}
		switch($args[0]){
			case "on":
			case "yes":
			case "open":
			case "true":
			case "unwhite":
			case "unwhitelist":
				$faction->setWhitelisted(false);
				$faction->sendMessage("Your faction is now open for joining.", Faction::CHAT_ANNOUNCEMENT);
				return "";
			case "off":
			case "no":
			case "close":
			case "false":
			case "white":
			case "whitelist":
				$faction->setWhitelisted(true);
				$faction->sendMessage("Your faction is now whitelisted.", Faction::CHAT_ANNOUNCEMENT);
				return "";
		}
		return $faction->isOpen() ? "Your faction is open for joining.":"Your faction is whitelisted.";
	}
	public function getUsage(){
		return "[on|off]";
	}
	public function getDescription(){
		return "Set your faction's open-for-joining status";
	}
}
