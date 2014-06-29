<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Kick extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "kick");
	}
	public function getDescription(){
		return "Kick a player";
	}
	public function getUsage(){
		return "<name>";
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_KICK_PLAYER);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		if($faction->hasMember($name = array_shift($args))){
			$members = $faction->getMembers(true);
			unset($members[strtolower($name)]);
			$faction->setMembers($members);
			$faction->sendMessage("$name has been kicked by " . $player->getName() . ". Reason: " . implode(" ", $args));
			if(($p = $this->getMain()->getServer()->getPlayerExact($name)) instanceof Player){
				$p->sendMessage("You have been kicked from the faction. Reason: " . implode(" ", $args));
			}
		}
		return self::NO_PLAYER;
	}
}
