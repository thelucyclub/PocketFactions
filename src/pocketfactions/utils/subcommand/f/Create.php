<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;

class Create extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "create");
	}
	public function onRun(array $args, Player $player){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		$name = $args[0];
		if(preg_replace($this->main->getFactionNamingRule(), "", $name) !== ""){
			return $this->getMain()->getFactionNameErrorMsg();
		}
		elseif($this->getMain()->getFList()->getFaction($name) instanceof Faction){
			return "A faction with name \"$name\" already exists!";
		}
		Faction::newInstance($name, $player->getName(), Rank::defaults($this->getMain()),
			Rank::defaultRank($this->getMain()), Rank::defaultAllyRank($this->getMain()),
			Rank::defaultTruceRank($this->getMain()), $this->main);
		$this->main->getServer()->broadcast("[PF] A new faction called $name has been created!", Server::BROADCAST_CHANNEL_USERS);
		return "";
	}
	public function checkPermission(Player $player){
		return $this->main->getFList()->getFaction($player) === false;
	}
	public function getDescription(){
		return "Create a faction";
	}
	public function getUsage(){
		return "<faction name>";
	}
}
