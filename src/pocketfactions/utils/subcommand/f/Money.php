<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Money extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "money");
	}
	public function getUsage(){
		return "<>";
	}
	public function checkPermission(Faction $faction, Player $player){
		// TODO
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args)){
			return self::WRONG_USE;
		}
		switch($cmd = array_shift($args)){
			case "view":
				$player->sendMessage("Bank balance: \$".$faction->getAccount(Faction::BANK)->getAmount());
				return "Cash balance: \$".$faction->getAccount(Faction::CASH)->getAmount();
		}
		return self::WRONG_USE;
	}
	public function getDescription(){
		return "Manage faction money";
	}
}
