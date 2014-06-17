<?php

namespace pocketfactions\utils\subcommand;

use pocketfactions\faction\Faction;
use pocketfactions\Main;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\Player;

abstract class Subcommand{
	const ALL = 0;
	const CONSOLE = 1;
	const PLAYER = 2;
	const FACTION_MEMBER = 3;
	const DB_LOADING = true;
	const WRONG_USE = false;
	const NO_PLAYER = 3;
	const NO_FACTION = 4;
	const NO_PERM = 5;
	const WRONG_FACTION = 6;
	/** @var string */
	protected $name;
	private $callable, $permCheck;
	private $issuer = self::ALL;
	/**
	 * @param Main $main
	 * @param $name
	 * @param string $callable
	 * @param string $permCheck
	 */
	public function __construct(Main $main, $name, $callable = "onRun", $permCheck = "checkPermission"){
		$this->main = $main;
		$this->name = $name;
		$rc = new \ReflectionClass($this);
		$this->callable = $callable;
		$this->permCheck = $permCheck;
		try{
			$method = $rc->getMethod($permCheck); // I think @shoghicp will say that there would be undefined behaviour again...
			$args = $method->getParameters();
			if(isset($args[0])){
				$class = $args[0]->getClass();
				if($class instanceof \ReflectionClass){
					switch($class->getName()){
						case "pocketmine\\Player":
							$this->issuer = self::PLAYER;
							break;
						case "pocketmine\\command\\ConsoleCommandSender":
							$this->issuer = self::CONSOLE;
							break;
						case "pocketfactions\\faction\\Faction":
							$this->issuer = self::FACTION_MEMBER;
							break;
					}
				}
			}
		}catch(\ReflectionException $ex){
			trigger_error(get_class($this) . " passed constructor to parent constructor with invalid argument 4 callable \"$callable\"", E_USER_ERROR);
			return;
		}
	}
	public final function run(array $args, CommandSender $sender){
		if($this->issuer === self::CONSOLE and !($sender instanceof ConsoleCommandSender)){
			$sender->sendMessage("Please run this command in-game.");
			return;
		}
		if(($this->issuer === self::PLAYER or $this->issuer === self::FACTION_MEMBER) and !($sender instanceof Player)){
			$sender->sendMessage("Please run this command on-console.");
			return;
		}
		if($this->issuer === self::FACTION_MEMBER){
			$f = $this->main->getFList()->getFaction($sender);
			if($f === null){
				$result = self::DB_LOADING;
			}elseif($f === false){
				$result = self::NO_FACTION;
			}else{
				$result = call_user_func(array($this, $this->callable), $args, $f, $sender);
			}
		}else{
			$result = call_user_func(array($this, $this->callable), $args, $sender);
		}
		if(is_string($result)){
			$sender->sendMessage($result);
			return;
		}
		if($result === self::DB_LOADING){
			$sender->sendMessage("The database is still loading!");
			return;
		}
		if($result === self::WRONG_USE){
			$sender->sendMessage("Usage: {$this->getUsage()}");
			return;
		}
		switch($result){
			case self::NO_PLAYER:
				$sender->sendMessage("Player not found!");
				break;
			case self::NO_FACTION:
				$sender->sendMessage("You must be in a faction!");
				break;
			case self::NO_PERM:
				$sender->sendMessage("You don't have permission to do this!");
				break;
			case self::WRONG_FACTION:
				$sender->sendMessage("Faction not found!");
				break;
		}
		return;
	} // I made these functions final to avoid accidental override
	public final function getName(){
		return $this->name;
	}
	public final function getMain(){
		return $this->main;
	}
	public abstract function getDescription();
	public abstract function getUsage();
	/**
	 * @param CommandSender $sender
	 * @return bool
	 */
	public final function hasPermission(CommandSender $sender){
		$callable = array($this, $this->permCheck);
		if($this->issuer === self::CONSOLE and !($sender instanceof ConsoleCommandSender) or $this->issuer === self::PLAYER and !($sender instanceof Player)){
			return false;
		}
		if($this->issuer === self::FACTION_MEMBER){
			if(!($sender instanceof Player) or !(($f = $this->main->getFList()->getFaction($sender)) instanceof Faction)){
				return false;
			}
			return call_user_func($callable, $f, $sender);
		}
		return call_user_func($callable, $sender);
	}
}
