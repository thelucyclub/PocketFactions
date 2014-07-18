<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\State;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\PlayerSubcommand;
use pocketmine\Player;

class Info extends PlayerSubcommand{
	public function __construct(Main $main){
		parent::__construct($main, "info");
	}
	public function getUsage(){
		return "<faction>";
	}
	public function checkPermission(Player $player){
		return true;
	}
	public function onRun(array $args, Player $player){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		$faction = $this->getMain()->getFList()->getFactionBySimilarName($name = array_shift($args));
		if($faction === null){
			return self::DB_LOADING;
		}
		if(!($faction instanceof Faction)){
			return "Faction $name not found!";
		}
		$name = $faction->getName();
		$motto = $faction->getMotto();
		$membersCnt = count($faction->getMembers());
		$allyCnt = 0;
		$truceCnt = 0;
		$enemyCnt = 0;
		$db = $this->getMain()->getFList()->getDb();
		$op = $db->prepare("SELECT relid FROM factions_rels WHERE smallid = :id OR largeid = :id;");
		$op->bindValue(":id", $faction->getID());
		$result = $op->execute();
		while(is_array($array = $result->fetchArray(SQLITE3_ASSOC))){
			switch($array["relid"]){
				case State::REL_ALLY:
					$allyCnt++;
					break;
				case State::REL_ENEMY:
					$enemyCnt++;
					break;
				case State::REL_TRUCE:
					$truceCnt++;
					break;
			}
		}
		$founder = $faction->getFounder();
		$power = $faction->getPower();
		$chunksCnt = count($faction->getChunks());
		return <<<INFO
Information of faction "$name":
Motto: $motto
Number of members: $membersCnt
Founder: $founder
Social status: $allyCnt ally factions, $truceCnt truce factions, $enemyCnt enemy factions
Power: $power
Number of claimed chunks: $chunksCnt
INFO;
	}
	public function getDescription(){
		return "View the information of a faction";
	}
}
