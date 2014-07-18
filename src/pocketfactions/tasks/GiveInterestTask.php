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
					// TODO Do something if the faction cannot pay the overdraft interest (bankrupt)
					// ideas: clear all cash, unclaim all chunks, disband, etc.
					return;
				}
				$account->pay($main->getXEconService(), $interest, "Bank overdraft interest");
			}
			$cnt++;
		}
		$main->getServer()->broadcastMessage("An interest of $percent% has been given to $cnt active factions.");
	}
}
