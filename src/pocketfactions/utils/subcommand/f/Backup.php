<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\utils\TextFormat;

class Backup extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "backup");
	}
	public function getDescription(){
		return "Create a backup of all factions asynchronously";
	}
	public function getUsage(){
		return "";
	}
	public function checkPermission(ConsoleCommandSender $s){
		return true;
	}
	public function onRun(){
		$file = $this->getMain()->getDataFolder();
		$file .= "backups/";
		@mkdir($file);
		$file .= "Factions backup at ".date(DATE_ATOM).".dat.bak";
		$res = fopen($file, "wb");
		if(!is_resource($res)){
			return TextFormat::RED."[ERROR] Failed to open stream to write to file: \"$file\". Check if a folder with the same name exists.";
		}
		$this->getMain()->getFList()->saveTo($res);
		return "An asynchronous backup is being written to $file.";
	}
}
