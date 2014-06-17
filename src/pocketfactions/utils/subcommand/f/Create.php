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
		$this->main->getServer()->broadcast("A new faction called $name has been created!", Server::BROADCAST_CHANNEL_USERS);
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
