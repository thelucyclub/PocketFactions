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
	const V_INIT = "\0x00";
	const V_CURRENT = "\0x00";
	private $flist;
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
		$this->flist = new FactionList($this->getDataFolder()."database/factions.dat");
		$this->config = new Config($this->getDataFolder()."database/data.json", Config::JSON, array(
			"next-fid" => 0
		));
	}
	protected function registerPerms(){
		$me = strtolower(self::NAME);
		$root = $this->regPerm("$me", "Allow using everything of PocketFactions");
		$cmd = $this->regPerm("$me.cmd.f", "Allow using main command /f", null, $root);
		$adminCmd = $this->regPerm("$me.cmd.fmgr", "Allow using main command /fmgr", Permission::DEFAULT_OP, $root);
		$create = $this->regPerm("$me.create", "Allow creating a faction", null, $root);
		$claim = $this->regPerm("$me.claim", "Allow claiming a chunk", null, $root);
		$unclaim = $this->regPerm("$me.unclaim", "Allow unclaiming a chunk", null, $root);
		$this->regPerm("$me.unclaimall", "Allow unclaiming all chunks in once", null, $unclaim);
	}
	public function regPerm($name, $desc, $default = null, $parent = null){
		if($default === null){
			$default = Permission::DEFAULT_TRUE;
		}
		elseif(is_bool($default)){
			$default = $default ? Permission::DEFAULT_TRUE:Permission::DEFAULT_FALSE;
		}
		elseif($default === 2){
			$default = Permission::DEFAULT_OP;
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
		$main->setPermission("pocketfactions.cmd.f");
		$main->setAliases(array("f"));
		$main->reg();
		//Faction Commands for Server Admins
		$main2 = new PCmd("fmanager", $this);
		$main2->setUsage("/fmanager <wclaim|smoney|gmoney|fdelete>");
		$main2->setDescription("PocketFactions Admin commands");
		$main2->setPermission("pocketfactions.cmd.fmgr");
		$main2->setAliases(array("fmgr"));
		$main2->reg();
	}
	public function getFList(){
		return $this->flist;
	}
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin(self::NAME);
	}
}
