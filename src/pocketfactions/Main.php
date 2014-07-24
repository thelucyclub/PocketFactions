<?php

namespace pocketfactions;

use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\faction\State;
use pocketfactions\tasks\CheckInactiveFactionsTask;
use pocketfactions\tasks\GiveInterestTask;
use pocketfactions\utils\FactionList;
use pocketfactions\utils\subcommand\f\Claim;
use pocketfactions\utils\subcommand\f\Create;
use pocketfactions\utils\subcommand\f\Disband;
use pocketfactions\utils\subcommand\f\Donate;
use pocketfactions\utils\subcommand\f\Home;
use pocketfactions\utils\subcommand\f\Homes;
use pocketfactions\utils\subcommand\f\Info;
use pocketfactions\utils\subcommand\f\Invite;
use pocketfactions\utils\subcommand\f\Join;
use pocketfactions\utils\subcommand\f\Kick;
use pocketfactions\utils\subcommand\f\ListSubcmd;
use pocketfactions\utils\subcommand\f\Loan;
use pocketfactions\utils\subcommand\f\Money;
use pocketfactions\utils\subcommand\f\Motto;
use pocketfactions\utils\subcommand\f\Perm;
use pocketfactions\utils\subcommand\f\Quit;
use pocketfactions\utils\subcommand\f\RmHome;
use pocketfactions\utils\subcommand\f\Sethome;
use pocketfactions\utils\subcommand\f\Setopen;
use pocketfactions\utils\subcommand\f\Siege;
use pocketfactions\utils\subcommand\f\Unclaim;
use pocketfactions\utils\subcommand\f\Unclaimall;
use pocketfactions\utils\subcommand\SubcommandMap;
use pocketfactions\utils\WildernessFaction;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\Player;
use pocketmine\plugin\PluginBase as Prt;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Main extends Prt implements Listener{
	const NAME = "PocketFactions";
	const XECON_SERV_NAME = "PocketFactionsMoney"; // any better names?
	const XECON_LOAN_SERV = "PocketFactionsLoans";
	const V_INIT = "\x00";
	const V_CURRENT = "\x00";
	/**
	 * The constant to put in the config for activating a faction upon game quit of a player who:
	 * spawned in the server
	 */
	const ACTIVITY_JOIN = 0;
	/**
	 * The constant to put in the config for activating a faction upon game quit of a player who:
	 * authenticated in the server using SimpleAuth
	 */
	const ACTIVITY_AUTH = 1;
	/**
	 * The constant to put in the config for activating a faction upon game quit of a player who:
	 * edited the factions world
	 */
	const ACTIVITY_BUILD = 2;
	public static $ACTIVITY_DEFINITION;
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
	private $worlds = [];
	private $cachedDefaultRanks = [];
	public function onEnable(){
		$this->getLogger()->info(TextFormat::LIGHT_PURPLE."Initializing...", false, 1);
		$this->getLogger()->info(TextFormat::LIGHT_PURPLE."Loading database...");
		$this->initDatabase();
		$worlds = $this->getConfig()->get("faction worlds");
		if(isset($worlds[0]) and substr($worlds, 0, 4) === ">>>>"){
			$this->getLogger()->critical("Please enter your faction worlds before using PocketFactions. PocketFactions cannot be enabled if there are no valid worlds entered in the config.");
			$this->setEnabled(false); // SUICIDE!
			return;
		}
		else{
			$this->worlds = $worlds;
			foreach($worlds as $offset => $world){
				if(!$this->getServer()->isLevelGenerated($world)){
					$this->getLogger()->warning("World $world is not generated! This world will not become a faction world.");
					unset($this->worlds[$offset]);
					continue;
				}
				if(!$this->getServer()->isLevelLoaded($world)){
					$this->getServer()->loadLevel($world);
				}
			}
		}
		/** @var \xecon\Main $xEcon */
		$xEcon = $this->getServer()->getPluginManager()->getPlugin("xEcon");
		$service = $xEcon->getService();
		$service->registerService(self::XECON_SERV_NAME);
		$service->registerService(self::XECON_LOAN_SERV);
		$this->getServer()->getScheduler()->scheduleDelayedRepeatingTask(new CheckInactiveFactionsTask($this), $this->getInactiveCheckInterval() * 1200, $this->getInactiveCheckInterval() * 1200);
		$this->getServer()->getScheduler()->scheduleRepeatingTask(new GiveInterestTask($this), $this->getReceiveInterestInterval());
		$this->registerEvents();
		$this->registerCmds();
		$this->declareActivityDefinition();
		echo PHP_EOL;
		$this->getLogger()->info(TextFormat::toANSI(TextFormat::GREEN . " Done!" . TextFormat::RESET . PHP_EOL));
	}
	public function onDisable(){
		$this->getFList()->save();
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
			new Donate($this), // donate to own faction
			new Home($this), // teleport to faction home(s)
			new Homes($this), // view faction home list
			new Info($this), // view information of a faction
			new Invite($this), // send request to other to join own faction
			new Join($this), // send requeset to faction to join it
			new Kick($this), // kick one from own faction
			new ListSubcmd($this), // list all active factions
			new Loan($this), // manage loan of own faction
			new Money($this), // manage own faction's money
			new Motto($this), // change own faction's motto
			new Perm($this), // manage own faction's permissions
//			new Rel($this), // manage own faction's relations with another
			new Quit($this), // quit current faction, and pass ownership to somebody if is owner
			new RmHome($this), // remove current faction's home
			new Sethome($this), // add/move current faction's home(s)
			new Setopen($this), // view/set whitelist on/off of own faction
			// new Siege($this),
			new Unclaim($this), // unclaim chunk
			new Unclaimall($this) // unclaim all claimed chunks
		];
		if($this->isSiegingEnabled()){
			$subcmds[] = new Siege($this);
		}
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
			return; // wilderness faction allows free building
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
		$p->sendMessage("You can't build at the claimed chunk of faction $cf!");
		return;
	}
	/**
	 * @param BlockPlaceEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onBlockPlace(BlockPlaceEvent $event){
		if(self::$ACTIVITY_DEFINITION === self::ACTIVITY_BUILD){
			$this->onLoggedIn($event->getPlayer());
		}
	}
	/**
	 * @param BlockBreakEvent $event
	 * @priority MONITOR
	 * @ignoreCancelled true
	 */
	public function onBlockBreak(BlockBreakEvent $event){
		if(self::$ACTIVITY_DEFINITION === self::ACTIVITY_BUILD){
			$this->onLoggedIn($event->getPlayer());
		}
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
		if(self::$ACTIVITY_DEFINITION === self::ACTIVITY_JOIN){
			$this->onLoggedIn($evt->getPlayer());
		}
	}
	public function onLoggedIn(Player $player){
		if(isset($this->loggedIn[$player->getID()])){
			return;
		}
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
	public function getXEconLoanService(){
		/** @var \xecon\Main $xEcon */
		$xEcon = $this->getServer()->getPluginManager()->getPlugin("xEcon");
		return $xEcon->getService()->getService(self::XECON_LOAN_SERV);
	}
	////////////
	// CONFIG //
	////////////
	// to make it easier to debug
	public function isFactionWorld($world){
		return in_array(strtolower($world), array_map("strtolower", $this->getConfig()->get("faction worlds")));
	}
	public function getDefaultRanks(){
		if(is_array($this->cachedDefaultRanks)){
			return $this->cachedDefaultRanks;
		}
		/** @var Rank[] $out */
		$out = [];
		$rels = [];
		foreach($this->getConfig()->get("default ranks") as $rank){
			$id = $rank["id"];
			if(isset($out[$id])){
				$this->getLogger()->warning("Default rank ID $id is duplicated! ".
					"Only the first one will be used.");
				continue;
			}
			$perms = 0;
			if(isset($ranks["permissions"])){
				foreach($rank["permissions"] as $origPerm){
					$perm = $origPerm;
					$inverse = false;
					if(substr($perm, 0, 1) === "!"){
						$perm = substr($perm, 1);
						$inverse = true;
					}
					if(defined($path = get_class()."::$perm")){
						if($inverse){
							$perms &= ~constant($path);
						}
						else{
							$perms |= constant($path);
						}
					}
					else{
						$this->getLogger()->warning("Undefined permission node: $perm. This permission will be ignored.");
					}
				}
			}
			$out[$id] = new Rank($id, $rank["name"], $perms, isset($rank["description"]) ? $rank["description"]:"");
			if(isset($rank["parent"])){
				if($rank["parent"] >= $id){
					$this->getLogger()->error("Parent rank ID must be smaller than child rank ID! ".
						"(Rank ID $id < ".$rank["parent"].".) Some bugs might occur if you don't stop ".
						"the server and fix it.");
				}
				$rels[$id] = $rank["parent"];
			}
		}
		ksort($rels, SORT_NUMERIC); // no more recursiveness :)
		foreach($rels as $child => $parent){
			$out[$child]->setPermsRaw($out[$child]->getPerms() | $out[$parent]->getPerms());
		}
		$this->cachedDefaultRanks = $out;
		return $out;
	}
	public function getDefaultRank(){
		return $this->getConfig()->get("default rank");
	}
	public function getDefaultAllyRank(){
		return $this->getConfig()->get("ally rank");
	}
	public function getDefaultTruceRank(){
		return $this->getConfig()->get("truce rank");
	}
	public function getDefaultStdRank(){
		return $this->getConfig()->get("standard rank");
	}
	public function getSiegeReputationLoss(){
		return $this->getConfig()->get("siege reputation loss");
	}
	public function getRelationReputationModifiers(){
		return $this->getConfig()->get("relation reputation modifiers");
	}
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
	public function declareActivityDefinition(){
		$def = $this->getConfig()->get("faction activity definition");
		if(!defined($path = get_class()."::ACTIVITY_".strtoupper($def))){
			$this->getLogger()->alert("Activity definition \"".$def."\" is undefined. The default (JOIN) will be used.");
			self::$ACTIVITY_DEFINITION = self::ACTIVITY_JOIN;
		}
		else{
			self::$ACTIVITY_DEFINITION = constant($path);
		}
		if(self::$ACTIVITY_DEFINITION === self::ACTIVITY_AUTH){
			if(!class_exists("Simpleauth\\event\\PlayerAuthenticateEvent")){
				$this->getLogger()->error("SimpleAuth is not loaded. Default activity definition (JOIN) will be used.");
				self::$ACTIVITY_DEFINITION = self::ACTIVITY_JOIN;
			}
		}
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
	public function getReceiveInterestInterval(){
		return $this->getConfig()->get("bank interest receive interval");
	}
	public function getBankLoanTypesRaw(){
		return $this->xeconConfig->get("loan types");
	}
	public function getLoan($type){
		$raw = $this->getBankLoanTypesRaw();
		if(!isset($raw[$type])){
			return null;
		}
		return $raw[$type];
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
	public function getNewHomeFee(){
		return $this->xeconConfig->get("new home fee");
	}
	public function getMoveHomeFee(){
		return $this->xeconConfig->get("move home fee");
	}
	public function getRmHomeFee(){
		return $this->xeconConfig->get("remove home fee");
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
}
