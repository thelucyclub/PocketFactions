<?php

namespace pocketfactions\tasks;

use pocketmine\scheduler\PluginTask;

class CheckInactiveFactionsTask extends PluginTask{
	public function onRun($ticks){
		/** @var \pocketfactions\Main $main */
		$main = $this->getOwner();
		$list = $main->getFList();
		$op = $list->getDb()->prepare("SELECT id FROM factions WHERE lastactive < :timeout;");
		$op->bindValue(":timeout", time() - $main->getMaxInactiveTime() * 3600);
		$result = $op->execute();
		while(is_array($array = $result->fetchArray(SQLITE3_ASSOC))){
			$list->disband($list->getFaction($array["id"]));
		}
	}
}
