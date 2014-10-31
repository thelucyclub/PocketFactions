<?php

namespace pocketfactions\tasks;

use pocketfactions\faction\Faction;
use pocketmine\scheduler\PluginTask;

class GiveInterestTask extends PluginTask{
	public function onRun($ticks){
		/** @var \pocketfactions\Main $main */
		$main = $this->getOwner();
		$percent = $main->getRandomBankInterestPercentage();
		$db = $main->getFList()->getDb();
		$op = $db->prepare("SELECT id FROM factions WHERE lastactive > :minactive;");
		$op->bindValue(":minactive", time() - $main->getSemiInactiveTime() * 3600);
		$result = $op->execute();
		$cnt = 0;
		while(is_array($array = $result->fetchArray(SQLITE3_ASSOC))){
			$faction = $main->getFList()->getFaction($array["id"]);
			$account = $faction->getAccount(Faction::BANK);
			$amount = $account->getAmount();
			if($amount < 0 and !$main->isInterestTakenForOverdraft()){
				continue;
			}
			$interest = $amount * $percent / 100;
			if($amount > 0){
				$main->getXEconService()->pay($account, $interest, "Bank interest");
			}
			elseif($amount < 0){
				if(!$account->canPay($interest)){
					return;
				}
				$account->pay($main->getXEconService(), $interest, "Bank overdraft interest");
			}
			foreach($faction->getLoans() as $loan){
				$loan->updateInterest();
				if($loan->isExpired()){
					$faction->sendMessage("A loan of \${$loan->getOriginalAmount()} plus \${$loan->getInterest()} interest has expired. Bank money is automatically taken from your faction to repay.");
					$bank = $faction->getAccount(Faction::BANK);
					$i = 0;
					$ops = $main->getBankruptOps();
					while(!$bank->canPay($loan->getAmount())){
						$op = $ops[$i++];
						switch(strtolower($op)){
							case "deposit all cash":
								$cash = $faction->getAccount(Faction::CASH);
								$bank = $faction->getAccount(Faction::BANK);
								if($cash->canPay($loan->getAmount())){
									$depositable = $loan->getAmount();
								}
								else{
									$depositable = $cash->getAmount();
								}
								$cash->pay($bank, $depositable, "Mandatory deposit due to bankrupt");
								break;
							case "unclaim all chunks":
								$faction->unclaimAll();
								break;
							case "do nothing":
								$continue = true;
								break;
							case "disband":
								$faction->sendMessage("Your faction has been disbanded because of loan bankrupt!", Faction::CHAT_ALL);
								$main->getFList()->disband($faction);
								$continue = true;
								break 2;
						};
					}
					if(isset($continue) and $continue === true){
						continue;
					}
					$bank->pay($loan, $loan->getAmount(), "Auto repay expired loan");
					$faction->removeLoan($loan);
				}
			}
			$cnt++;
		}
		$main->getServer()->broadcastMessage("An interest of $percent% has been given to $cnt active factions.");
	}
}
