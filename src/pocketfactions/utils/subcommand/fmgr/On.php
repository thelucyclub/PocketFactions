<?php

namespace pocketfactions\utils\subcommand\fmgr;

use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

class On extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "on");
	}
	public function onRun(array $args, Player $player){
	}
	public function checkPermission(Player $player){
		return $player->isOp();
	}
}
