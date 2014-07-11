<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Unclaim extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "unclaim");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_UNCLAIM);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		$chunk = Chunk::fromObject($player);
		return ($result = $faction->unclaim($chunk)) === true ? "The chunk you are standing in has been unclaimed.":$result;
	}
	public function getDescription(){
		return "Unclaim the chunk you are standing on";
	}
	public function getUsage(){
		return "";
	}
}
