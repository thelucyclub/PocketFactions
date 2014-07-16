<?php

namespace pocketfactions\utils\subcommand\fmgr;

use pocketfactions\Main;
use pocketfactions\utils\subcommand\PlayerSubcommand;
use pocketmine\Player;

class Off extends PlayerSubcommand{
	public function __construct(Main $main){
		parent::__construct($main, "off");
	}
	public function getDescription(){
		return "Turn off your admin bypass mode";
	}
	public function getUsage(){
		return "";
	}
	public function checkPermission(Player $player){
		return $this->getMain()->getAdminMode($player);
	}
	public function onRun(array $args, Player $player){
		$this->getMain()->setAdminMode($player, false);
		return "Your admin bypass mode is now off.";
	}
}
