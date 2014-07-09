<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Sethome extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "sethome");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $this->getMain()->getMaxHomes() > 0 and $faction->getMemberRank($player)->hasPerm(Rank::P_SET_HOME) and (($cf = $this->getMain()->getFList()->getFaction(Chunk::fromObject($player))) instanceof Faction) and $cf->getID() === $faction->getID();
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args[0])){
			$args[0] = "default";
		}
		$homes = $faction->getHomes();
		if(isset($homes[$args[0]])){
			$faction->setHome($args[0], $player); // a new position will be created instead of the player position
			$faction->sendMessage("Faction home $args[0] has been moved to " . $player->getName() . "'s location.", Faction::CHAT_ANNOUNCEMENT);
			return "";
		}
		if(count($homes) + 1 > ($max = $this->getMain()->getMaxHomes())){
			return "A faction can only set a maximum of $max home(s)!";
		}
		$faction->setHome($args[0], $player);
		$faction->sendMessage("A new home $args[0] has been created at " . $player->getName() . "'s location.", Faction::CHAT_ANNOUNCEMENT);
		return "";
	}
	public function getDescription(){
		return "Set your faction's (named) home";
	}
	public function getUsage(){
		return $this->getMain()->getMaxHomes() > 1 ? "<name>":"[name]";
	}
}
