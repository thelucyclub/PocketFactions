<?php

namespace pocketfactions\cmd\general;

use pocketfactions\PocketFactions;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\ConsoleCommandSender;
use pocketmine\command\PluginIdentifiableCommand;

class SubcommandMap extends Command implements PluginIdentifiableCommand{
	/** @var PocketFactions */
	private $plugin;
	/** @var Subcommand[] */
	private $subs = [];
	public function __construct(PocketFactions $plugin, $name, $desc, $aliases){
		$this->plugin = $plugin;
		parent::__construct($name, $desc, "/$name help [page = 1] [lines = 5]", is_array($aliases) ? $aliases:[[$aliases]]);
	}
	public function execute(CommandSender $sender, $lbl, array $args){
		if(!isset($args[0])){
			$args[0] = "help";
		}
		if($args[0] === "help"){
			$this->showHelpTo($sender, isset($args[1]) ? intval($args[1]):1, isset($args[2]) ? intval($args[2]) : ($sender instanceof ConsoleCommandSender ? PHP_INT_MAX:5));
		}
		elseif(isset($this->subs[$args[0]])){
			$this->subs[array_shift($args)]->execute($sender, $args);
		}
		return true;
	}
	/**
	 * @return PocketFactions
	 */
	public function getPlugin(){
		return $this->plugin;
	}
	private function showHelpTo(CommandSender $sender, $page, $lines){
		if($lines <= 0){
			$sender->sendMessage("Lines must not be less than 1");
		}
		$max = (int) ceil(count($this->subs) / $lines);
		$page = max(1, min($max, $page));
		$sender->sendMessage("Showing /{$this->getName()} page $page of $max:");
		/** @var Subcommand[] $cmds */
		$cmds = array_values($this->subs);
		for($i = ($page - 1) * $lines;
			    $i < $page * $lines and isset($cmds[$i]) and $cmds[$i]->canUse($sender);
			    $i++){
			$sender->sendMessage($cmds[$page]->getUsageMessage($sender));
		}
	}
	public function register(Subcommand $sub){
		$this->subs[$sub->getName()] = $sub;
		$aliases = $sub->getAliases();
		if(is_array($aliases)){
			foreach($aliases as $alias){
				$this->subs[$alias] = $sub;
			}
		}
		elseif(is_string($aliases)){
			$this->subs[$aliases] = $sub;
		}
	}
}
