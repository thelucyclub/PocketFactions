<?php

namespace pocketfactions\utils\subcommand\f;

use legendofmcpe\statscore\StatsCore;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\faction\State;
use pocketfactions\Main;
use pocketfactions\utils\request\RelationModifyRequest;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Rel extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "rel");
	}
	public function getDescription(){
		return "Configure factionr relations";
	}
	public function getUsage(){
		return "<faction> <enemy|ally|truce|neutral> [extra message ...]";
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_REL_SET);
	}
	public function onRun(array $args, Faction $faction){
		if(!isset($args[1])){
			return self::WRONG_USE;
		}
		$other = $this->getMain()->getFList()->getFaction(array_shift($args));
		if(!($other instanceof Faction)){
			return self::NO_FACTION;
		}
		switch(strtolower($args[1])){
			case "e":
			case "enemy":
			case "enemies":
				$intent = State::REL_ENEMY;
				break;
			case "a":
			case "ally":
			case "allies":
			case "alliance":
				$intent = State::REL_ALLY;
				break;
			case "n":
			case "neutral":
			case "peace":
				$intent = State::REL_NEUTRAL;
				break;
			case "t":
			case "truce":
				$intent = State::REL_TRUCE;
				break;
		}
		if(!isset($intent)){
			return self::WRONG_USE;
		}
		$current = $this->getMain()->getFList()->getFactionsState($faction, $other);
		if($current === $intent){
			return "Your current faction relation with ".$other->getDisplayName()."is already ".array_shift($args).".";
		}
		if($intent < $current){ // set to a worse relation
			$this->getMain()->getFList()->setFactionsState(new State($faction, $other, $intent));
		}
		array_shift($args);
		$message = implode(" ", $args);
		$request = new RelationModifyRequest($faction, $other, $intent, $message);
		StatsCore::getInstance()->getRequestList()->add($request);
		$faction->sendMessage("A request to change to ".self::getState($intent)." relation with ".$other->getDisplayName()." has been sent.");
		return "";
	}
	public static function getState($id){
		switch($id){
			case State::REL_NEUTRAL:
				return "neutral";
			case State::REL_ALLY:
				return "ally";
			case State::REL_ENEMY:
				return "enemy";
			case State::REL_TRUCE:
				return "truce";
		}
		return false;
	}
}
