<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;

abstract class FactionMemberSubcommand extends Subcommand{
	public function __construct(Main $main, $name){
		parent::__construct($main, $name);
	}
	public abstract function checkPermission(Faction $faction, Player $player);
	public abstract function onRun(array $args, Faction $faction, Player $player);
}
