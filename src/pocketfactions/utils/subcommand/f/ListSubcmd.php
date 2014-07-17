<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;

class ListSubcmd extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "list");
	}
	public function getUsage(){
		return "";
	}
	public function checkPermission(){
		return true;
	}
	public function onRun(){
		$factions = [];
		$op = $this->getMain()->getFList()->getDb()->prepare("SELECT name FROM factions WHERE lastactive > :minactive;");
		$op->bindValue(":minactive", time() - $this->getMain()->getSemiInactiveTime() * 3600);
		$result = $op->execute();
		while(is_array($array = $result->fetchArray(SQLITE3_ASSOC))){
			$factions[] = $array["name"];
		}
		return "List of ".count($factions)." active factions on this server:\n".implode(", ", $factions);
	}
	public function getDescription(){
		return "Get a list of factions active in the past ".$this->getMain()->getSemiInactiveTime()." hour(s)";
	}
}
