<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class RmHome extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "rmhome");
	}
	public function checkPermission(Faction $faction, Player $player){
		$rank = $faction->getMemberRank($player);
		$account = $this->getMain()->getRmHomeFee()["account"];
		return $rank->hasPerm(Rank::P_SET_HOME) and $rank->hasPerm($account === "bank" ? Rank::P_SPEND_MONEY_BANK:Rank::P_SPEND_MONEY_CASH);
	}
	public function onRun(array $args, Faction $faction){
		if(!isset($args[0])){
			$name = "default";
		}
		else{
			$name = array_shift($args);
		}
		$fee = $this->getMain()->getRmHomeFee();
		$account = $faction->getAccount($fee["account"]);
		if(!$account->canPay($fee["amount"])){
			return "Your faction don't have enough ".$fee["account"]." money to remove a home.";
		}
		$account->pay($this->getMain()->getXEconService(), $fee["amount"], "Home removal");
		return $faction->rmHome($name) ? "$name home has been removed.":"Home \"".$name."\" not found.";
	}
	public function getUsage(){
		return "[name]";
	}
	public function getDescription(){
		return "Removes a faction home";
	}
}
