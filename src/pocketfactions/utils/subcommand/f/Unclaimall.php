<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketmine\Player;

class Unclaimall extends FactionMemberSubcommand{
	public function __construct(Main $main){
		parent::__construct($main, "unclaimall");
	}
	public function getUsage(){
		return "";
	}
	public function getDescription(){
		return "Unclaim all claimed chunks";
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_UNCLAIM_ALL);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		$faction->unclaimAll();
		$faction->sendMessage("All chunks have been unclaimed!");
		return "";
	}
}
