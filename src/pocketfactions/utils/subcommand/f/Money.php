<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Money extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "money");
	}
	public function getUsage(){
		return "<view|withdraw|deposit> [[faction] amount]";
	}
	public function checkPermission(Faction $faction, Player $player){
		$rank = $faction->getMemberRank($player);
		foreach([
			Rank::P_DEPOSIT_MONEY_BANK,
			Rank::P_DEPOSIT_MONEY_CASH,
			Rank::P_SPEND_MONEY_BANK,
			Rank::P_SPEND_MONEY_CASH
		] as $perm){
			if($rank->hasPerm($perm)){
				return true;
			}
		}
		return false;
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args)){
			return self::WRONG_USE;
		}
		$rank = $faction->getMemberRank($player);
		switch($cmd = array_shift($args)){
			case "view":
			case "balance":
				$player->sendMessage("Bank balance: \$".$faction->getAccount(Faction::BANK)->getAmount());
				return "Cash balance: \$".$faction->getAccount(Faction::CASH)->getAmount();
			case "withdraw":
				if(!$rank->hasPerm(Rank::P_SPEND_MONEY_BANK) or !$rank->hasPerm(Rank::P_DEPOSIT_MONEY_CASH)){
					return self::NO_PERM;
				}
				if(!isset($args[0])){
					return self::WRONG_USE;
				}
				$amount = array_shift($args);
				$bank = $faction->getAccount(Faction::BANK);
				$cash = $faction->getAccount(Faction::CASH);
				if(!$bank->canPay($amount)){
					return "You don't have so much money in your faction bank!";
				}
				$bank->pay($cash, $amount, "Cash withdrawal");
				return "\$$amount has been withdrawn from your faction bank.";
			case "deposit":
				if(!$rank->hasPerm(Rank::P_SPEND_MONEY_CASH) or !$rank->hasPerm(Rank::P_DEPOSIT_MONEY_BANK)){
					return self::NO_PERM;
				}
				if(!isset($args[0])){
					return self::WRONG_USE;
				}
				$amount = array_shift($args);
				$bank = $faction->getAccount(Faction::BANK);
				$cash = $faction->getAccount(Faction::CASH);
				if(!$cash->canPay($amount)){
					return "Your faction don't have so much cash to deposit!";
				}
				$cash->pay($bank, $amount, "Cash deposit");
				return "\$$amount of cash has been deposited into your faction bank.";
			case "ff":
				if(!isset($args[1])){
					return "Usage: /f money ff <faction> <amount>";
				}
				$other = $this->getMain()->getFList()->getFactionBySimilarName(array_shift($args));
				$amount = (int) array_shift($args);
				if($amount <= 0){
					return "Amount must be larger than zero.";
				}
				$account = $faction->getAccount(Faction::CASH);
				if(!$account->canPay($amount)){
					return "Your faction doesn't have enough cash!";
				}
				$otherAccount = $other->getAccount(Faction::CASH);
				$account->pay($otherAccount, $amount, "Transfer using /f money ff");
				$other->sendMessage("\$$amount cash has been transferred from faction $faction.");
				return "\$$amount cash has been transferred to faction $other.";
			case "pf":
				return "Please use \"/f donate\" instead.";
			case "export":
				// TODO
				return "TODO";
		}
		return self::WRONG_USE;
	}
	public function getDescription(){
		return "Manage faction money";
	}
}
