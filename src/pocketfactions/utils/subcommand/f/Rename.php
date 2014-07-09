<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Rename extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "main");
	}
	public function getDescription(){
		return "Rename your faction";
	}
	public function getUsage(){
		return "<new name>";
	}
	public function onRun(array $args, Faction $faction){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		$fee = $this->getMain()->getFactionRenameFee();
		$account = $faction->getAccount($fee["account"]);
		$amount = $account->getAmount();
		if($amount < 0 - $this->getMain()->getMaxBankOverdraft() and $fee["account"] === "bank" or $amount < 0){
			return "Your faction doesn't have enough money to rename :( consider taking loans or donating money?";
		}
		$faction->setName($args[0]);
		$faction->sendMessage("The faction name has been changed to $args[0]!");
		return "";
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_RENAME);
	}
}
