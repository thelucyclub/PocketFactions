<?php

namespace pocketfactions\utils\subcommand\fmgr;

use pocketfactions\Main;
use pocketfactions\utils\subcommand\PlayerSubcommand;
use pocketmine\Player;

class On extends PlayerSubcommand{
	public function __construct(Main $main){
		parent::__construct($main, "on");
	}
	public function onRun(array $args, Player $player){
		$this->getMain()->setAdminMode($player, true);
		return "Your admin bypass mode is now on.";
	}
	public function checkPermission(Player $player){
		return !$this->getMain()->getAdminMode($player);
	}
	public function getDescription(){
		return "Turn on your admin bypass mode";
	}
	public function getUsage(){
		return "";
	}
}
