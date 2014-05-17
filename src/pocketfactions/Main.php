<?php

namespace pocketfactions;

use pocketfactions\io\Database;
use pocketfactions\utils\PluginCmd as PCmd;

use pocketmine\Player;
use pocketmine\Server;
use pocketmine\command\Command;
use pocketmine\command\CommandSender as Issuer;
use pocketmine\event\EventPriority;
use pocketmine\event\Listener;
use pocketmine\permission\Permission;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\EventExecutor as EvtExe;
use pocketmine\plugin\PluginBase as Prt;
use pocketmine\utils\TextFormat as Font;

require_once(dirname(__FILE__)."/functions.php");

class Main extends Prt implements Listener, EvtExe{
	const NAME = "PocketFactions";
	private $db;
	public function onEnable(){
		console("Initializing", false, 1);
		$this->initDatabase();
		echo ".";
		$this->registerPerms();
		echo ".";
		$this->registerEvents();
		echo ".";
		$this->registerCmds();
		echo " Done!".PHP_EOL.Font::RESET;
	}
	protected function initDatabase(){
		$this->db = new Database($this->getDataFolder()."db/");
	}
	protected function registerPerms(){
		$me = strtolower(self::NAME);
		$root = $this->regPerm("$me", "Allow using everything of PocketFactions");
		$cmd = $this->regPerm("$me.cmd", "Allow using main command /f", null, $root);
		$create = $this->regPerm("$me.create", "Allow creating a faction", null, $root);
	}
	public function regPerm($name, $desc, $default = null, $parent = null){
		if($default === null){
			$default = Permission::DEFAULT_TRUE;
		}
		elseif(is_bool($default)){
			$default = $default ? Permission::DEFAULT_TRUE:Permission::DEFAULT_FALSE;
		}
		return DefaultPermissions::registerPermission(new Permission($name, $desc, $default), $parent);
	}
	protected function registerEvents(){
	}
	protected function event($simpleName, $priority = EventPriority::NORMAL){
		$this->getServer()->getPluginManager()->registerEvent("pocketmine\\event\\$simpleName", $this, $priority, $this, $this);
	}
	protected function registerCmds(){
	//Faction Commands for Players
		$main = new PCmd("faction", $this);
		$main->setUsage("/faction <help|cmd>");
		$main->setDescription("PocketFactions commands");
		$main->setPermission("pocketfactions.cmd");
		$main->setAliases(array("f"));
		$main->reg();
	//Faction Commands for Server Owners
		$main2 = new PCmd("fmanager", $this);
		$main2->setUsage("/fmanager <wclaim|smoney|gmoney|fdelete>");
		$main2->setDescription("PocketFactions Admin commands");
		$main2->setPermission("admin.pocketfactions.cmd");
		$main2->setAliases(array("frmgr"));
		$main2->reg();
	}
	public function getDb(){
		return $this->db;
	}
	public function onCommand(Issuer $issuer, Command $cmd, $lbl, array $args){
	switch($cmd->getName()){
	
		case "fmgr":
		case "faction":
				if(!($issuer instanceof Player)){
				$issuer->sendMessage("Type '/f help' for the lists of commands.");
				if($issuer instanceof Console)
					$isuer->sendMessage("Run this command in-game.");
				return true;
			}
			$subcmd = @array_shift($args);
			switch($subcmd){ // manage subcommand
				case "help":
				if(isset($args[0])){
						if(strtolower($args[0]) === "1"){
							$issuer->sendMessage("-=[ Pocket Faction Commands (P.1/3) ]=-");
							$issuer->sendMessage("/f create - Create a Faction.");
							$issuer->sendMessage("/f invite - Invite someone in your Faction.");
							$issuer->sendMessage("/f accept - Accept Faction Invitation.");
							$issuer->sendMessage("/f decline - Decline Faction Invitation.");
							$issuer->sendMessage("/f join - Join public Faction.");
							break;
						}
						if(strtolower($args[0]) === "2"){
							$issuer->sendMessage("-=[ Pocket Faction Commands (P.2/3) ]=-");
							$issuer->sendMessage("/f claim - Claim areas for your Faction.");
							$issuer->sendMessage("/f unclaim - Unclaim areas by your Faction.");
							$issuer->sendMessage("/f unclaimall - Unclaim all areas by your Faction.");
							$issuer->sendMessage("/f kick - Kick someone in your Faction.");
							$issuer->sendMessage("/f setperm - Set permissions in your Faction.");
							$issuer->sendMessage("/f sethome - Set Faction home.");
							break;
						}
						if(strtolower($args[0]) === "3"){
							$issuer->sendMessage("-=[ Pocket Faction Commands (P.3/3) ]=-");
							$issuer->sendMessage("/f home - Teleport back to Faction home.");
							$issuer->sendMessage("/f money - View Faction Money balance.");
							$issuer->sendMessage("/f quit - Quit a Faction.");
							$issuer->sendMessage("/f disband - Disband your Faction.");
							break;
						}
					}
					$issuer->sendMessage("-=[ Pocket Faction Commands (P.1/3) ]=-");
					$issuer->sendMessage("/f create - Create a Faction.");
					$issuer->sendMessage("/f invite - Invite someone in your Faction.");
					$issuer->sendMessage("/f accept - Accept Faction Invitation.");
					$issuer->sendMessage("/f decline - Decline Faction Invitation.");
					$issuer->sendMessage("/f join - Join public Faction.");
					break;
				
				case "create":
				//TODO code
				break;
				
				case "invite":
				//TODO code
				break;
				
				case "accept":
				//TODO code
				break;
				
				case "decline":
				//TODO code
				break;
				
				case "join":
				//TODO code
				break;
				
				case "claim":
				//TODO code
				break;
				
				case "unclaim":
				//TODO code
				break;
				
				case "unclaimall"
				//TODO code
				break;
				
				case "kick":
				//TODO code
				break;
				
				case "setperm":
				//TODO code
				break;
				
				case "sethome":
				//TODO code
				break;
				
				case "home":
				//TODO code
				break;
				
				case "money":
				//TODO code
				break;
				
				case "quit":
				//TODO code
				break;
				
				case "disband":
				//TODO code
				break;
				
	}
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin(self::NAME);
	}
}
