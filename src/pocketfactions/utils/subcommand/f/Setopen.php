<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Setopen extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "setopen");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_SET_WHITE);
	}
	public function onRun(array $args, Faction $faction){
		if(!isset($args[0])){
			$args[0] = "check";
		}
		switch($args[0]){
			case "on":
			case "yes":
			case "open":
			case "true":
			case "unwhite":
			case "unwhitelist":
				$fee = $this->getMain()->getSetOpenFee();
				$account = $faction->getAccount($fee["account"]);
				if($account->canPay($fee["amount"])){
					return "Your faction doesn't have enough ".$account->getName()." money to turn off white-list!";
				}
				$account->pay($this->getMain()->getXEconService(), $fee["amount"], "Opening faction for joining");
				$faction->setWhitelisted(false);
				$faction->sendMessage("Your faction is now open for joining.", Faction::CHAT_ANNOUNCEMENT);
				return "";
			case "off":
			case "no":
			case "close":
			case "false":
			case "white":
				case "whitelist":
				$fee = $this->getMain()->getSetNotOpenFee();
				$account = $faction->getAccount($fee["account"]);
				if($account->canPay($fee["amount"])){
					return "Your faction doesn't have enough ".$account->getName()." money to turn on white-list!";
				}
				$account->pay($this->getMain()->getXEconService(), $fee["amount"], "White-listing faction");
				$faction->setWhitelisted(true);
				$faction->sendMessage("Your faction is now white-listed.", Faction::CHAT_ANNOUNCEMENT);
				return "";
		}
		return $faction->isOpen() ? "Your faction is open for joining.":"Your faction is white-listed.";
	}
	public function getUsage(){
		return "[on|off]";
	}
	public function getDescription(){
		return "Set your faction's open-for-joining status";
	}
	public function getAliases(){
		return ["so", "open", "o"];
	}
}
