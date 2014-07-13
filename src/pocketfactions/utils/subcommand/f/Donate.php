<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;
use xecon\entity\PlayerEnt;

class Donate extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "donate");
	}
	public function getUsage(){
		return "<amount>";
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_DEPOSIT_MONEY_BANK);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		/** @var \xecon\Main $xEcon */
		$xEcon = $this->getMain()->getServer()->getPluginManager()->getPlugin("xEcon");
		$ent = $xEcon->getPlayerEntity($player);
		$account = $ent->getAccount(PlayerEnt::ACCOUNT_BANK);
		$amount = (int) array_shift($args);
		if($account->canPay($amount)){
			$factionAccount = $faction->getAccount("Bank");
			$account->pay($factionAccount, $amount, "Donation");
		}
		$faction->sendMessage("Receiveed a donation of \$$amount from ".$player->getName()."!");
		return "\$$amount has been deposited into the faction bank from your bank account.";
	}
	public function getDescription(){
		return "Donate money into your faction";
	}
}
