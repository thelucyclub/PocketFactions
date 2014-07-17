<?php

namespace pocketfactions;

use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\faction\State;
use pocketfactions\tasks\CheckInactiveFactionsTask;
use pocketfactions\utils\FactionList;
use pocketfactions\utils\subcommand\f\Claim;
use pocketfactions\utils\subcommand\f\Create;
use pocketfactions\utils\subcommand\f\Disband;
use pocketfactions\utils\subcommand\f\Home;
use pocketfactions\utils\subcommand\f\Homes;
use pocketfactions\utils\subcommand\f\Invite;
use pocketfactions\utils\subcommand\f\Join;
use pocketfactions\utils\subcommand\f\Kick;
use pocketfactions\utils\subcommand\f\ListSubcmd;
use pocketfactions\utils\subcommand\f\Money;
use pocketfactions\utils\subcommand\f\Motto;
use pocketfactions\utils\subcommand\f\Perm;
use pocketfactions\utils\subcommand\f\Quit;
use pocketfactions\utils\subcommand\f\Sethome;
use pocketfactions\utils\subcommand\f\Setopen;
use pocketfactions\utils\subcommand\f\Unclaim;
use pocketfactions\utils\subcommand\f\Unclaimall;
use pocketfactions\utils\subcommand\SubcommandMap;
use pocketfactions\utils\WildernessFaction;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\permission\Permission;
use pocketmine\Player;
use pocketmine\plugin\PluginBase as Prt;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends Prt implements Listener{
	const NAME = "PocketFactions";
	const XECON_SERV_NAME = "PocketFactionsCharges"; // any better names?
	const V_INIT = "\x00";
	const V_CURRENT = "\x00";
	/** @var bool[] */
	private $adminModes = [];
	/**
	 * @var Config
	 */
	public $cleanSave;
	public $haveRequiredSimpleAuth = false;
	/**
	 * @var Config
	 */
	private $xeconConfig;
	/**
	 * @var string[][] Unread inbox messages indexed with lowercase player name
	 */
	/**
	 * @var FactionList
	 */
	private $flist;
	/** @var WildernessFaction */
	private $wilderness;
	/**
	 * @var bool[] (all elements should be true)
	 */
	private $loggedIn = array();
	private $fCmd;
	private $fmCmd;
	public function onEnable(){
		$this->getLogger()->info(TextFormat::AQUA . "Initializing", false, 1);
		$this->initDatabase();
		/** @var \xecon\Main $xEcon */
		$xEcon = $this->getServer()->getPluginManager()->getPlugin("xEcon");
		$service = $xEcon->getService();
		$service->registerService(self::XECON_SERV_NAME);
		echo ".";
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CheckInactiveFactionsTask($this), $this->getInactiveCheckInterval() * 1200, $this->getInactiveCheckInterval() * 1200);
		$this->registerEvents();
		echo ".";
		$this->registerCmds();
		echo PHP_EOL;
		$this->getLogger()->info(TextFormat::toANSI(TextFormat::GREEN . " Done!" . TextFormat::RESET . PHP_EOL));
	}
	protected function initDatabase(){
		$this->flist = new FactionList($this); // used AsyncTask because the server could be running in the middle
		$this->wilderness = new WildernessFaction($this);
		@mkdir($this->getDataFolder() . "database/");
		echo ".";
		$this->cleanSave = new Config($this->getDataFolder() . "database/data.json", Config::JSON, ["next-fid" => 10, // 10 IDs left for defaults
		]);
		$this->saveDefaultConfig();
		$this->saveResource("xecon.yml");
		$this->reloadConfig();
		$this->xeconConfig = new Config($this->getDataFolder() . "xecon.yml", Config::YAML);
	}
	public function getXEconConfig(){
		return $this->xeconConfig;
	}
	/**
	 * @param Player $player
	 * @return bool
	 */
	public function getAdminMode(Player $player){
		return isset($this->adminModes[$player->getID()]) ? $this->adminModes[$player->getID()]:false;
	}
	/**
	 * @param Player $player
	 * @param bool $on
	 */
	public function setAdminMode(Player $player, $on){
		$this->adminModes[$player->getID()] = $on;
	}
	private function regPermWithObject(Permission $perm, Permission $parent = null){
		if($parent instanceof Permission){
			$parent->getChildren()[$perm->getName()] = true;
			return $this->regPermWithObject($perm);
		}
		$this->getServer()->getPluginManager()->addPermission($perm);
		return $this->getServer()->getPluginManager()->getPermission($perm->getName());
	}
	private function registerEvents(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		if(class_exists($event = "SimpleAuth\\event\\PlayerAuthenticateEvent")){
			$this->haveRequiredSimpleAuth = true;
			$this->getServer()->getPluginManager()->registerEvents(new SimpleAuthListener($this), $this);
		}
	}
	private function registerCmds(){
		$this->fCmd = new SubcommandMap("factions", $this, "Factions main command", "pocketfactions.cmd.factions", ["f"]);
		$this->fmCmd = new SubcommandMap("factions-manager", $this, "Factions manager command", "pocketfactions.cmd.factionsmanager", ["fadm", "fmgr"]);
		$subcmds = [ // a list of all /f subcommands. /f help no need to register here.
			new Claim($this), // claim a chunk
			new Create($this), // create a faction
			new Disband($this), // disband own faction
			new Home($this), // teleport to faction home(s)
			new Homes($this), // view faction home list
			new Invite($this), // send request to other to join own faction
			new Join($this), // send requeset to faction to join it
			new Kick($this), // kick one from own faction
			new ListSubcmd($this), // list all active factions
			new Money($this), // manage own faction's money
			new Motto($this), // change own faction's motto
			new Perm($this), // manage own faction's permissions
			// new Rel($this), // manage own faction's relations with another
			new Quit($this), // quit current faction, and pass ownership to somebody if is owner
			new Sethome($this), // set current faction's home(s)
			new Setopen($this), // view/set whitelist on/off of own faction
			new Unclaim($this), // unclaim chunk
			new Unclaimall($this) // unclaim all claimed chunks
		];
		$this->fCmd->registerAll($subcmds);
		$this->getServer()->getCommandMap()->registerAll("pocketfactions", [$this->fCmd, $this->fmCmd]);
	}
	/**
	 * @param PlayerInteractEvent $evt
	 * @priority HIGH
	 * @ignoreCancelled true
	 */
	public function onBlockTouch(PlayerInteractEvent $evt){
		$p = $evt->getPlayer();
		if($this->getAdminMode($p)){
			return;
		}
		$cf = $this->getFList()->getFaction(Chunk::fromObject($evt->getBlock()));
		if($cf === false){
			$cf = $this->getWilderness();
		}
		$pf = $this->getFList()->getFaction($p);
		if($pf === false){ // doesn't join any factions
			if($cf instanceof WildernessFaction){
				return;
			}
			$evt->setCancelled();
			return;
		}
		$rel = $this->getFList()->getFactionsState($cf, $pf);
		if($rel === State::REL_ALLY){
			if($pf->getMemberRank($p)->hasPerm($cf->isCentreLocation($evt->getBlock()) ? Rank::P_BUILD_CENTRE:Rank::P_BUILD)){
				return;
			}
			$evt->setCancelled();
			return;
		}
		$evt->setCancelled();
		$p->sendMessage("You cannot build at the claimed chunk of faction $cf!");
		return;
	}
	/**
	 * @param EntityDamageByEntityEvent $event
	 * @priority HIGH
	 * @ignoreCancelled true
	 */
	public function onFight(EntityDamageByEntityEvent $event){
		$victim = $event->getEntity();
		$faction = $this->getFList()->getFaction(Chunk::fromObject($victim));
		if(!$faction->canFight($event->getDamager(), $event->getEntity())){
			$event->setCancelled();
		}
	}
	/**
	 * @return string
	 */
	public function getFactionsFilePath(){
		return $this->getDataFolder() . "database/factions.dat";
	}
	public function onLogin(PlayerJoinEvent $evt){
		if(!$this->haveRequiredSimpleAuth){
			$this->onLoggedIn($evt->getPlayer());
		}
	}
	public function onLoggedIn(Player $player){
		$f = $this->getFList()->getFaction($player);
		if(!($f instanceof Faction)){
			return;
		}
		$f->setActiveNow();
		$this->loggedIn[$player->getID()] = true;
	}
	public function onQuit(PlayerQuitEvent $evt){
		$cid = $evt->getPlayer()->getID();
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
	public function getCleanSaveConfig(){
		return $this->cleanSave;
	}
	/**
	 * @return Config
	 */
	public function getUserConfig(){
		return $this->getConfig();
	}
	/**
	 * @return FactionList
	 */
	public function getFList(){
		return $this->flist;
	}
	public function getWilderness(){
		return $this->wilderness;
	}
	public function getXEconService(){
		/** @var \xecon\Main $xEcon */
		$xEcon = $this->getServer()->getPluginManager()->getPlugin("xEcon");
		return $xEcon->getService()->getService(self::XECON_SERV_NAME);
	}
	////////////
	// CONFIG //
	////////////
	// to make it easier to debug
	public function getClaimSingleChunkPower(){
		return $this->getConfig()->get("power required to claim a chunk");
	}
	public function getPowerGainPerOnlineHour(){
		return $this->getConfig()->get("power gained per online hour");
	}
	public function getPowerLossPerOfflineDay(){
		return $this->getConfig()->get("power loss per offline FULL day");
	}
	public function getPowerGainPerKill($type = "default"){
		if($type === "player"){
			return $this->getConfig()->get("power gained per player kill");
		}
		$data = $this->getConfig()->get("power gained per mob kill");
		if(isset($data[$type])){
			return $data[$type];
		}
		return $data["default"];
	}
	public function getPowerLossPerDeath($type = "default"){
		$data = $this->getConfig()->get("power loss per death");
		return isset($data[$type]) ? $data[$type]:$data["default"];
	}
	public function isSiegingEnabled(){
		return $this->getConfig()->get("enable sieging");
	}
	public function getSiegeRadius(){
		return $this->isSiegingEnabled() ? $this->getConfig()->get("siege radius"):-1;
	}
	public function getLevelGenerationSeed(){
		return $this->getConfig()->get("level generation seed");
	}
	public function getFactionNamingRuleRaw(){
		return $this->getConfig()->get("faction naming rule");
	}
	public function getFactionNameMinLength(){
		return $this->getConfig()->get("faction name min length");
	}
	public function getFactionNameMaxLength(){
		return $this->getConfig()->get("faction name max length");
	}
	public function getFactionNamingRule(){
		return str_replace(["@min", "@max"], [(string) $this->getFactionNameMinLength(), (string) $this->getFactionNameMaxLength()], $this->getFactionNamingRuleRaw());
	}
	public function getFactionNameErrorMsg(){
		return $this->getConfig()->get("faction name reject message");
	}
	public function getMaxHomes(){
		return $this->getConfig()->get("max homes");
	}
	public function getMaxInactiveTime(){
		return $this->getConfig()->get("faction inactive time");
	}
	public function getInactiveCheckInterval(){
		return $this->getConfig()->get("faction inactive check interval");
	}
	public function getSemiInactiveTime(){
		return $this->getConfig()->get("faction semi-inactive time");
	}
	/////////////////
	// XECON STUFF //
	/////////////////
	// xEcon things
	public function getDefaultCash(){
		return $this->xeconConfig->get("default cash");
	}
	public function getDefaultBank(){
		return $this->xeconConfig->get("default bank");
	}
	public function getMaxCash(){
		return $this->xeconConfig->get("max cash");
	}
	public function getMaxBank(){
		return $this->xeconConfig->get("max bank");
	}
	public function getExternalMoneyInventoryTypesRaw(){
		return $this->xeconConfig->get("inventory types");
	}
	public function getRandomBankInterestPercentage(){
		return mt_rand((int) ($this->xeconConfig->get("bank interest range minimum") * 100), (int) ($this->xeconConfig->get("bank interest range maximum") * 100)) / 100;
	}
	public function getBankLoanTypesRaw(){
		return $this->xeconConfig->get("loan types");
	}
	public function getMaxBankOverdraft(){
		return $this->xeconConfig->get("bank max overdraft");
	}
	public function isInterestTakenForOverdraft(){
		return $this->xeconConfig->get("bank overdraft take interest");
	}
	public function getMaxLiability(){
		return $this->xeconConfig->get("max liability");
	}
	public function getChunkClaimFee(){
		return $this->xeconConfig->get("chunk claim fee");
	}
	public function getChunkUnclaimRepay(){
		return $this->xeconConfig->get("chunk unclaim repay");
	}
	public function getFactionRenameFee(){
		return $this->xeconConfig->get("faction rename charge");
	}
	public function getSetOpenFee(){
		return $this->xeconConfig->get("set open");
	}
	public function getSetNotOpenFee(){
		return $this->xeconConfig->get("set not open");
	}
	public function getRankChangingCharge(){
		return $this->xeconConfig->get("rank changing charge");
	}
	public function getAddRankCharge(){
		return $this->xeconConfig->get("rank adding charge");
	}
	public function getRmRankCharge(){
		return $this->xeconConfig->get("rank removing charge");
	}
	public function getFounderWithdrawableAccounts(){
		return $this->xeconConfig->get("accounts withdrawable to founder");
	}
	//	/**
	//	 * LOL
	//	 */
	//	public function suicide(){
	//		$this->setEnabled(false);
	//	}
}
