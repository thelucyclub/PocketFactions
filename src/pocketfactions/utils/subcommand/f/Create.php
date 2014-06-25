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
			return false;
		}
		$name = $args[0];
		if(preg_replace($this->main->getFactionNamingRule(), "", $name) !== ""){
			return $this->getMain()->getFactionNameErrorMsg();
		}
		Faction::newInstance($name, $player->getName(), Rank::defaults(), Rank::defaultRank(), $this->main, Position::fromObject($player, $player->getLevel()));
		$this->main->getServer()->broadcast("[PF] A new faction called $name has been created!", Server::BROADCAST_CHANNEL_USERS);
		/*
		* Why does it have a faction base? Making a faction base point is quite hassle to be honest.
		* Didn't we talked about this already? Storing money in a chest would cause trouble later on.
		* For instance, we could just use the imaginary bank to store the faction deposit money in
		* the bank. What if the player creates his faction at the spawn point?
		*/
		$this->main->getServer()->broadcast("Faction $name is created by " . $player->getDisplayName() . " based at " . $player->getX() . ", " . $player->getY() . ", " . $player->getZ() . " in world " . $player->getLevel()->getName(), Server::BROADCAST_CHANNEL_ADMINISTRATIVE);
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
