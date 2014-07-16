<?php

namespace pocketfactions\utils\subcommand;

use pocketfactions\Main;
use pocketmine\Player;

abstract class PlayerSubcommand extends Subcommand{
	public function __construct(Main $main, $name){
		parent::__construct($main, $name);
	}
	public abstract function checkPermission(Player $player);
	public abstract function onRun(array $args, Player $player);
}
