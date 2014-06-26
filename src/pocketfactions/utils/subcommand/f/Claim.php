<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Claim extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "claim");
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!$faction->canClaimMore()){
			return "[PF] Your faction does not have power to\n[PF] claim any more chunks!";
		}
		// TODO check if this chunk has already been claimed
		$success = $faction->claim(Chunk::fromObject($player));
		return $success ? "[PF] You have claimed this 16x16 chunk!":"[PF] Your faction does not have enough\n[PF] money to claim this chunk!";
	}
	public function checkPermission(Faction $faction, Player $player){
		$rank = $faction->getMemberRank($player);
		$perm = $rank->hasPerm(Rank::P_CLAIM);
		$fee = $this->getMain()->getChunkClaimFee();
		if($fee["amount"] === 0){
			return $perm;
		}
		if($fee["account"] === "bank"){
			return $perm and $rank->hasPerm(Rank::P_SPEND_MONEY_BANK);
		}
		return $perm and $rank->hasPerm(Rank::P_SPEND_MONEY_CASH);
	}
	public function getDescription(){
		return "Claim the chunk you are in.";
	}
	public function getUsage(){
		return "";
	}
}
