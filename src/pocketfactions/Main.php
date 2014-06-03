<?php

namespace pocketfactions;

use pocketfactions\faction\Faction;
use pocketfactions\utils\PluginCmd as PCmd;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerQuitEvent;
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
	 * @var RequestList $reqList
	 */
	private $reqList;
	/**
	 * @var CmdHandler
	 */
	private $cmdExe;
	/**
	 * @var Config
	 */
	public $cleanSave;
	/**
	 * @var
	 */
	public $userConfig;
	/**
	 * @var string[][] Unread inbox messages indexed with lowercase player name
	 */
	private $inbox = [];
	/**
	 * @var FactionList
	 */
	private $flist;
	/**
	 * @var bool[] (all elements should be true)
	 */
	private $loggedIn = array();
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
		$this->flist = new FactionList;
		$this->reqList = new RequestList;
		$this->cleanSave = new Config($this->getDataFolder()."database/data.json", Config::JSON, [
			"next-fid" => 0
		]);
		$this->userConfig = new Config($this->getDataFolder()."config.yml", Config::YAML, [
			"level generation default seed (leave as null for random)" => null,
		]);
	}
	protected function registerPerms(){
		$me = strtolower(self::NAME);
		$root = $this->regPerm("$me", "Allow using everything of PocketFactions");
		$this->regPerm("$me.cmd.f", "Allow using main command /f", null, $root);
		$this->regPerm("$me.cmd.fmgr", "Allow using main command /fmgr", Permission::DEFAULT_OP, $root);
		$this->regPerm("$me.create", "Allow creating a faction", null, $root);
		$this->regPerm("$me.invite", "Allow inviting players in a faction", null, $root);
		$this->regPerm("$me.accept", "Allow to accept faction request", null, $root);
		$this->regPerm("$me.decline", "Allow to decline faction request", null, $root);
		$this->regPerm("$me.join", "Allow join a faction", null, $root);
		$this->regPerm("$me.claim", "Allow claiming a chunk", null, $root);
		$unclaim = $this->regPerm("$me.unclaim", "Allow unclaiming a chunk", null, $root);
		$this->regPerm("$me.unclaimall", "Allow unclaiming all chunk", null, $root);
		$this->regPerm("$me.kick", "Allow to kick members in faction", null, $root);
		$this->regPerm("$me.setperm", "Allow to set permissions in faction", null, $root);
		$this->regPerm("$me.sethome", "Allow to set home of faction", null, $root);
		$this->regPerm("$me.setopen", "Allow to set faction available to public", null, $root);
		$this->regPerm("$me.home", "Allow to tp to faction home", null, $root);
		$this->regPerm("$me.money", "Allow to view faction money", null, $root); //requires xEcon plugin installed
		$this->regPerm("$me.quit", "Allow to quit a faction", null, $root);
		$this->regPerm("$me.disband", "Allow to disband a faction", null, $root);
		$this->regPerm("$me.motto", "Allow to set motto of faction", null, $root);
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
		$this->cmdExe = new CmdHandler;
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
	/**
	 * @param string $player
	 * @param string $msg
	 */
	public function addOfflineMessage($player, $msg){
		$player = strtolower($player);
		if(!isset($this->inbox[$player])){
			$this->inbox[$player] = [];
		}
		$this->inbox[$player][] = $msg;
	}
	public function onJoin(PlayerJoinEvent $evt){
		$name = strtolower($evt->getPlayer()->getName());
		if(isset($this->inbox[$name])){
			$evt->getPlayer()->sendMessage("You have ".count($this->inbox[$name])." messages in your offline inbox:");
			while(count($this->inbox[$name]) > 0){
				$evt->getPlayer()->sendMessage(array_shift($this->inbox[$name]));
			}
		}
	}
	/**
	 * @priority HIGH
	 */
	public function onBlockTouch(PlayerInteractEvent $evt){
		$f = $this->getFList()->getFaction($evt->getPlayer());
	}
	/**
	 * @return string
	 */
	public function getFactionsFilePath(){
		return $this->getDataFolder()."factions.dat";
	}
	public function onLogin(PlayerJoinEvent $evt){
		$f = $this->getFList()->getFaction($evt->getPlayer());
		if(!($f instanceof Faction)){
			return;
		}
		$f->setActiveNow();
		$this->loggedIn[$evt->getPlayer()->CID] = true;
	}
	public function onQuit(PlayerQuitEvent $evt){
		$cid = $evt->getPlayer()->CID;
		if(isset($this->loggedIn[$cid]) and $this->loggedIn[$cid] === true){
			$this->loggedIn[$cid] = false;
			unset($this->loggedIn[$cid]);
			$f = $this->getFList()->getFaction($evt->getPlayer());
			if($f instanceof Faction){
				$f->setActiveNow();
			}
		}
	}
	/**
	 * @return Config
	 */
	public function getConfig(){
		return $this->cleanSave;
	}
	/**
	 * @return Config
	 */
	public function getUserConfig(){
		return $this->userConfig;
	}
	/**
	 * @return FactionList
	 */
	public function getFList(){
		return $this->flist;
	}
	/**
	 * @return RequestList
	 */
	public function getReqList(){
		return $this->reqList;
	}
	/**
	 * @return static
	 */
	public static function get(){
		return Server::getInstance()->getPluginManager()->getPlugin(self::NAME);
	}
}
