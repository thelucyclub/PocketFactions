<?php

namespace pocketfactions\cmd\general;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat;

abstract class Subcommand{
	public abstract function canUse(CommandSender $sender);
	public function getUsageMessage(CommandSender $sender){
		$sender->sendMessage(
			TextFormat::AQUA . "/{$this->getName()} " .
			TextFormat::GREEN . $this->getUsage() . ": " .
			TextFormat::YELLOW . $this->getDescription()
		);
	}
	public abstract function getName();
	public function getUsage(){
		return "";
	}
	public function getDescription(){
		return "";
	}
	public abstract function getAliases();
	public abstract function execute(CommandSender $sender, array $args);
}
