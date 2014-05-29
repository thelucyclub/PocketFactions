<?php

namespace pocketfactions;

use pocketfactions\session\Invitation;
use pocketfactions\session\PendingOperation;
use pocketfactions\utils\PluginCmd as PCmd;

use pocketmine\Server;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\permission\Permission;
use pocketmine\permission\DefaultPermissions;
use pocketmine\plugin\PluginBase as Prt;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as Font;

require_once dirname(__FILE__)."/functions.php";

class Main extends Prt implements Listener{
	const NAME = "PocketFactions";
	const V_INIT = "\x00";
	const V_CURRENT = "\x00";
	/**
	 * @var Config
	 */
	public $cleanSave;
	/**
	 * @var PendingOperation[] pending operations indexed with the POID
	 */
	public $op = array();
	/**
	 * @var string[][] Unread inbox messages indexed with lowercase player name
	 */
	private $inbox = [];
	public function onEnable(){
		console(Font::AQUA."Initializing", false, 1);
		$this->initDatabase();
		echo ".";
		$this->registerPerms();
		echo ".";
		$this->registerEvents();
		echo ".";
		$this->registerCmds();
		echo Font::GREEN." Done!".Font::RESET.PHP_EOL;
	}
	protected function initDatabase(){
		$this->cleanSave = new Config($this->getDataFolder()."database/data.json", Config::JSON, array(
			"next-fid" => 0
		));
	}
	protected function registerPerms(){
		$me = strtolower(self::NAME);
		$root = $this->regPerm("$me", "Allow using everything of PocketFactions");
		$cmd = $this->regPerm("$me.cmd.f", "Allow using main command /f", null, $root);
		$adminCmd = $this->regPerm("$me.cmd.fmgr", "Allow using main command /fmgr", Permission::DEFAULT_OP, $root);
		$create = $this->regPerm("$me.create", "Allow creating a faction", null, $root);
		$invite = $this->regPerm("$me.invite", "Allow inviting players in a faction", null, $root);
		$accept = $this->regPerm("$me.accept", "Allow to accept faction request", null, $root);
		$decline = $this->regPerm("$me.decline", "Allow to decline faction request", null, $root);
		$join = $this->regPerm("$me.join", "Allow join a faction", null, $root);
		$claim = $this->regPerm("$me.claim", "Allow claiming a chunk", null, $root);
		$unclaim = $this->regPerm("$me.unclaim", "Allow unclaiming a chunk", null, $root);
		$unclaimall = $this->regPerm("$me.unclaimall", "Allow unclaiming all chunk", null, $root);
		$kick = $this->regPerm("$me.kick", "Allow to kick members in faction", null, $root);
		$setperm = $this->regPerm("$me.setperm", "Allow to set permissions in faction", null, $root);
		$sethome = $this->regPerm("$me.sethome", "Allow to set home of faction", null, $root);
		$setopen = $this->regPerm("$me.setopen", "Allow to set faction available to public", null, $root);
		$home = $this->regPerm("$me.home", "Allow to tp to faction home", null, $root);
		$money = $this->regPerm("$me.money", "Allow to view faction money", null, $root); //requires xEcon plugin installed
		$quit = $this->regPerm("$me.quit", "Allow to quit a faction", null, $root);
		$disband = $this->regPerm("$me.disband", "Allow to disband a faction", null, $root);
		$motto = $this->regPerm("$me.motto", "Allow to set motto of faction", null, $root);
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
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
	}
	protected function registerCmds(){
		$this->cmdExe = CmdHandler;
		//Faction Commands for Players
		$main = new PCmd("faction", $this, $this->cmdExe);
		$main->setUsage("/faction <help|cmd>");
		$main->setDescription("PocketFactions commands");
		$main->setPermission("pocketfactions.cmd.f");
		$main->setAliases(array("f"));
		$main->reg();
		//Faction Commands for Server Admins
		$main2 = new PCmd("fmanager", $this, $this->cmdExe);
		$main2->setUsage("/fmanager <wclaim|smoney|gmoney|fdelete>");
		$main2->setDescription("PocketFactions Admin commands");
		$main2->setPermission("pocketfactions.cmd.fmgr");
		$main2->setAliases(array("fmgr"));
		$main2->reg();
	}
	public function onPreCmd(PlayerCommandPreprocessEvent $evt){
		$cmd = strstr($evt->getMessage(), " ", true);
		if($cmd === "/poaccept"){
			$evt->setCancelled(true);
		}
		if($cmd === "/podeny"){
			$evt->setCancelled(true);
		}
		if($cmd === "/polist"){ // reminds me of politics or police
			$evt->setCancelled(true);
		}
	}
	public function addPendingOp(PendingOperation $op, $autoInvite = true){
		$this->op[$op->getID()] = $op;
		if($autoInvite and ($op instanceof Invitation)){
			$this->invitations[$op->getInvited()] = true;
		}
	}
	public function addOfflineMessage($player, $msg){
		if(!isset($this->inbox[$player])){
			$this->inbox[$player] = [];
		}
		$this->inbox[$player][] = $msg;
	}
	public function onJoin(PlayerJoinEvent $evt){
		if(isset($this->inbox[strtolower($evt->getPlayer()->getName())])){
			$msgs = $this->inbox[strtolower($evt->getPlayer()->getName())];
			if(count($msgs) === 0){
				return;
			}
			$evt->getPlayer()->sendMessage("Welcome back! You have ".count($msgs)." new inbox messages.");
			foreach($msgs as $k => $msg){
				$evt->getPlayer()->sendMessage("Offline message #$k: $msg");
			}
			$this->inbox[strtolower($evt->getPlayer()->getName())] = [];
		}
	}
	/**
	 * @return string
	 */
	public function getFactionsFilePath(){
		return $this->getDataFolder()."factions.dat";
	}
	/**
	 * @return Config
	 */
	public function getConfig(){
		return $this->cleanSave;
	}
	/**
	 * @return static
	 */
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin(self::NAME);
	}
}
