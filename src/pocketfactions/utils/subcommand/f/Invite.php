<?php

namespace pocketfactions\utils\subcommand\f;

use legendofmcpe\statscore\request\PlayerRequestable;
use legendofmcpe\statscore\StatsCore;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\request\FactionInviteRequest;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class Invite extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "invite");
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args[0])){
			return false;
		}
		$target = $this->main->getServer()->getPlayer($name = array_shift($args));
		if(!($target instanceof Player)){
			return self::NO_PLAYER;
		}
		/** @var StatsCore $statsCore */
		$statsCore = $this->getMain()->getServer()->getPluginManager()->getPlugin("StatsCore");
		$request = new FactionInviteRequest($faction, new PlayerRequestable($target), implode(" ", $args));
		$statsCore->getRequestList()->add($request);
		$faction->sendMessage("[PF] A request has been sent to $name (" . $target->getName() . ")\n[PF] by {$player->getName()}.\n[PF] Preview:\n============\n" . $request->getContent());
		return "";
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player->getName())->hasPerm(Rank::P_INVITE);
	}
	public function getDescription(){
		return "Invite a player to your faction";
	}
	public function getUsage(){
		return "<player> [extra message...]";
	}
	public function getAliases(){
		return ["i"];
	}
}
