<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;

class ListSubcmd extends Subcommand{
	const FILTER_NONE = 0x00;
	const FILTER_OPEN = 0x01;
	const FILTER_CLOSE = 0x02;
	public function __construct(Main $main){
		parent::__construct($main, "list");
	}
	public function getUsage(){
		return "[open|close]";
	}
	public function checkPermission(){
		return true;
	}
	public function onRun(){
		$filter = self::FILTER_NONE;
		if(isset($args[0])){
			if($args[0] === "open"){
				$filter |= self::FILTER_OPEN;
			}
			elseif($args[0]){
				$filter |= self::FILTER_CLOSE;
			}
		}
		$factions = [];
		$query = "SELECT name, open FROM factions WHERE lastactive > :minactive;";
		if($filter & 0x03){
			$query = "SELECT name FROM factions WHERE lastactive > :minactive AND open = ".($filter === self::FILTER_OPEN ? "1":"0").";";
		}
		$op = $this->getMain()->getFList()->getDb()->prepare($query);
		$op->bindValue(":minactive", time() - $this->getMain()->getSemiInactiveTime() * 3600);
		$result = $op->execute();
		while(is_array($array = $result->fetchArray(SQLITE3_ASSOC))){
			$factions[] = $array["name"].(isset($array["open"]) ?
					($array["open"] === 1 ? " (opened)":" (closed)"):"");
		}
		return "List of ".count($factions)." active factions on this server:\n".implode(", ", $factions);
	}
	public function getDescription(){
		return "Get a list of factions active in the past ".$this->getMain()->getSemiInactiveTime()." hour(s)";
	}
}
