<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketmine\Player;

class Loan extends FactionMemberSubcommand{
	public function __construct(Main $main){
		parent::__construct($main, "loan");
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_TAKE_DEBT);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		switch($sub = strtolower(array_shift($args))){
			case "types":
				return "Types of loans: ".implode(", ", array_keys($this->getMain()->getBankLoanTypesRaw()));
			case "view":
				$data = [
					"type" => ["type"],
					"amount" => ["amount"],
					"interest" => ["interest"],
					"creation" => ["borrowed at"],
					"due" => ["due at"],
				];
				foreach($faction->getLiabilities() as $liability){
					if($liability instanceof \xecon\account\Loan){
						$data["type"][] = strstr($liability->getName(), " ", true);
						$data["amount"] = "\$".$liability->getAmount();
						$data["interest"][] = "\$".($liability->getAmount() - $liability->getOriginalAmount());
						$data["creation"][] = date("M j, y H:i", $liability->getCreation());
						$data["due"][] = date("M j, y H:i", $liability->getDue());
					}
				}
				$out = "Your faction's loans: (current datetime is ".date("M j, y H:i:s").")\n";
				foreach($data as $key => $d){
					$max = max(array_map("strlen", $d));
					foreach($d as $k => $v){
						$data[$key][$k] .= str_repeat(" ", $max - strlen($v));
					}
				}
				for($i = 0; $i < count($data["type"]); $i++){
					$dat = [
						$data["type"][$i],
						$data["amount"][$i],
						$data["interest"][$i],
						$data["creation"][$i],
						$data["due"]];
					$out .= implode(" | ", $dat);
				}
				return $out;
			case "take":
				if(!isset($args[0])){
					return "Usage: /f loan take <type>\n[PF] <type> is listed at /f loan types";
				}
				$type = array_shift($args);
				return $faction->addLoan_faction($type);
			case "repay":
			case "return":
				if(!isset($args[0]) or !is_numeric($args[0])){
					return "Usage: /loan return <amount>"; // TODO filters
				}
				$amount = array_shift($args);
				$account = $faction->getAccount(Faction::CASH);
				if(!$account->canPay($amount)){
					return "Loans are repaid via cash. Please withdraw enough money for your faction's cash account.";
				}
				$picked = null;
				foreach($faction->getLiabilities() as $l){
					// TODO allow customization here via arg 3
					if($l instanceof \xecon\account\Loan){
						if(!($picked instanceof \xecon\account\Loan)){
							$picked = $l;
							continue;
						}
						if($picked->getDue() < $l->getDue()){
							$picked = $l;
							continue;
						}
						if($picked->getDue() === $l->getDue()){
							if($l->getIncreasePerHour() > $picked->getIncreasePerHour()){
								$picked = $l;
								continue;
							}
							elseif($l->getAmount() < $picked->getAmount()){
								$picked = $l;
							}
						}
					}
				}
				if($picked === null){
					return "You have no loans to repay!";
				}
				$account->pay($picked, $amount, "Repaid a loan");
		}
		return self::WRONG_USE;
	}
	public function getUsage(){
		return "<types|view|take|repay";
	}
	public function getDescription(){
		return "View/take/return loans of the faction";
	}
}
