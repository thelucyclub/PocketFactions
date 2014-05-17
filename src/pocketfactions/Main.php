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
		$main = new PCmd("faction", $this);
		$main->setUsage("/faction <help|cmd>");
		$main->setDescription("PocketFactions commands");
		$main->setPermission("pocketfactions.cmd");
		$main->setAliases(array("f"));
		$main->reg();
	}
	public function getDb(){
		return $this->db;
	}
	public function onCommand(Issuer $issuer, Command $cmd, $lbl, array $args){
	}
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin(self::NAME);
	}
}
