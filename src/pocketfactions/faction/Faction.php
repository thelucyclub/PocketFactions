<?php

namespace pocketfactions\faction;

use legendofmcpe\statscore\Requestable;
use legendofmcpe\statscore\StatsCore;
use pocketfactions\utils\IFaction;
use pocketfactions\Main;
use pocketmine\entity\Entity as MCEntity;
use pocketmine\inventory\InventoryHolder;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use xecon\account\DummyInventory;
use xecon\entity\Entity;

class Faction implements InventoryHolder, Requestable, IFaction{
	use Entity;

	// vars
	/** @var Main */
	protected $main;
	/** @var string $name */
	protected $name;
	/** @var string $motto */
	protected $motto;
	/** @var int $id */
	protected $id;
	/** @var string $founder */
	protected $founder;
	/** @var Rank[] $ranks indexed by internal rank IDs */
	protected $ranks;
	/** @var int $defaultRank internal rank ID of the default rank */
	protected $defaultRank;
	/**
	 * This array is a list keyed with lowercase member names and filled with Rank object references to $Faction->ranks.
	 * Use <code>array_keys()</code> to get a plain list of members.
	 * @var Rank[] $members
	 */
	protected $members;
	/** @var  int $lastActive */
	protected $lastActive;
	/** @var Chunk[] $chunks numerically keyed chunks with undefined order (possibly, but not sure, sequence of claiming) */
	protected $chunks;
	/** @var Chunk $baseChunk The base chunk o a faction. TODO should we remove it? Possibly yes. */
	protected $baseChunk;
	/** @var \pocketmine\level\Position $home */
	protected $home;
	/** @var bool */
	protected $whitelist;
	/** @var */
	protected $econEnt;
	/**
	 * @var Server
	 */
	public $server;
	//////////////////
	// constructors //
	//////////////////
	/**
	 * @param string $name
	 * @param string $founder name of the faction founder
	 * @param Rank[] $ranks
	 * @param int $defaultRankIndex the default rank's key in $ranks
	 * @param Main $main
	 * @param Position $home the home position of the faction
	 * @param string $motto
	 * @param bool $whitelist
	 * @param int|bool $id
	 * @return Faction
	 */
	public static function newInstance($name, $founder, array $ranks, $defaultRankIndex, Main $main, Position $home, $motto = "", $whitelist = true, $id = false){
		if(!is_int($id)){
			$id = self::nextID($main);
		}
		$data = ["name" => $name, "motto" => $motto, "id" => $id, "founder" => strtolower($founder), "ranks" => $ranks, "default-rank" => $ranks[$defaultRankIndex], "members" => [], "last-active" => time(), "chunks" => [], "home" => $home, "base-chunk" => Chunk::fromObject($home), "whitelist" => $whitelist];
		return new Faction($data, $main);
	}
	public function __construct(array $args, Main $main){
		$this->name = $args["name"];
		$this->motto = $args["motto"];
		$this->id = $args["id"];
		$this->founder = $args["founder"];
		$this->ranks = $args["ranks"];
		$this->defaultRank = $args["default-rank"];
		$this->members = $args["members"];
		$this->lastActive = $args["last-active"];
		$this->chunks = $args["chunks"];
		$this->home = $args["home"];
		$this->main = $main;
		//		$this->chunks = [];
		//		/** @var Chunk[] $chunks */
		//		$chunks = $args["chunks"];
		//		foreach($chunks as $c){
		//			if(!isset($this->chunks[$c->getLevel()])){
		//				$this->chunks[$c->getLevel()] = [];
		//			}
		//			$this->chunks[$c->getLevel()][$c->getX().",".$c->getZ()] = $c;
		//		}
		$this->baseChunk = $args["base-chunk"];
		$this->whitelist = $args["whitelist"];
		$this->server = Server::getInstance();
		$levels = [];
		foreach($this->chunks as $chunk){
			$level = $chunk->getLevel();
			if(!isset($levels[$level])){
				$levels[] = $level;
				if(!$this->server->isLevelLoaded($level)){
					if(!$this->server->isLevelGenerated($this->server->loadLevel($level))){
						$this->server->generateLevel($level, $this->main->getLevelGenerationSeed());
					}
					$this->server->loadLevel($level);
				}
			}
		}
	}
	///////////////////
	// API functions //
	////////////////////
	//// Getters and Setters
	/**
	 * @return string
	 */
	public function getName(){
		return $this->name;
	}
	/**
	 * @return string
	 */
	public function getMotto(){
		return $this->motto;
	}
	public function setMotto($motto){
		$this->motto = $motto;
	}
	/**
	 * @return int
	 */
	public function getID(){
		return $this->id;
	}
	/**
	 * @return string
	 */
	public function getFounder(){
		return $this->founder;
	}
	/**
	 * @return Rank[]
	 */
	public function getRanks(){
		return $this->ranks;
	}
	/**
	 * @return Rank
	 */
	public function getDefaultRank(){
		return $this->defaultRank;
	}
	/**
	 * @return string[] an array of names of members
	 */
	public function getMembers(){
		return array_keys($this->members);
	}
	/**
	 * @return Chunk[]
	 */
	public function getChunks(){
		return $this->chunks;
	}
	/**
	 * @return Chunk
	 */
	public function getBaseChunk(){
		return $this->baseChunk;
	}
	/**
	 * @param string $member
	 * @return Rank
	 */
	public function getMemberRank($member){
		return isset($this->members[strtolower($member)]) ? $this->members[strtolower($member)]:null;
	}
	/**
	 * @return bool
	 */
	public function isWhitelisted(){
		return $this->whitelist;
	}
	/**
	 * @param bool $white
	 */
	public function setWhitelisted($white){
		$this->whitelist = $white;
	}
	/**
	 * @return bool
	 */
	public function isOpen(){
		return !$this->whitelist;
	}
	/**
	 * @param bool $open
	 */
	public function setOpen($open){
		$this->whitelist = !$open;
	}
	/**
	 * @return \pocketmine\level\Position
	 */
	public function getHome(){
		return $this->home;
	}
	/**
	 * @return int
	 */
	public function getLastActive(){
		return $this->lastActive;
	}
	public function setActiveNow(){
		$this->lastActive = time();
	}
	public function hasChunk(Chunk $chunk){
		foreach($this->chunks as $cchunk){
			if($chunk->equals($cchunk)){
				return true;
			}
		}
		return false;
	}
	public function powerClaimable(){
		$power = $this->getPower();
		return (int) ($power / $this->main->getClaimSingleChunkPower());
	}
	public function getPower(){
		$power = 0;
		foreach($this->members as $mbr){
			$statsCore = StatsCore::getInstance();
			if(!($statsCore instanceof StatsCore) or $statsCore->isDisabled()){
				$this->main->getLogger()->error("StatsCore is not found or is disabled.");
			}
			$micro = $statsCore->getMLogger()->getTotalOnlineTime($mbr);
			$power += (((int) ($micro / 60 / 60)) * $this->main->getPowerGainPerOnlineHour());
			$power -= $statsCore->getInstance()->getMLogger()->getFullOfflineDays($mbr);
			// TODO add kills and deaths factors
		}
		return $power;
	}
	public function hasMember($name){
		return in_array(strtolower($name), $this->members);
	}
	//// Runnable API functions; Command-redirected functions
	/**
	 * @param Player $newMember
	 * @return bool|string $success <code>true</code> or reason that cannot join
	 */
	public function join(Player $newMember){
		$this->members[strtolower($newMember->getName())] = $this->getDefaultRank();
		return true;
	}
	/**
	 * @param string $memberName
	 */
	public function kick($memberName){
	}
	/**
	 * @param Chunk $chunk
	 * @return bool
	 */
	public function claim(Chunk $chunk){
		if(count($this->chunks) + 1 > $this->powerClaimable()){
			return false;
		}
		$this->chunks[] = $chunk;
		return true;
	}
	public function addLoan($name, $amount){
		if(isset($this->liabilities[$name])){
			// TODO decide where to save expire date
			// TODO idea: save number of loans, then each loan is an account instead of each loan type is an account
			$this->liabilities[$name]->add($amount);
			return true;
		}else{
			return false;
		}
	}
	public function __toString(){
		return $this->getName();
	}
	/////////////////////////
	// Inherited functions //
	/////////////////////////
	public function sendMessage($message){
		return null;
	}
	// xEcon-related
	public function getInventory(){
		return new DummyInventory($this, "Faction Account"); // TODO replace the dummy placeholder
	}
	public function getEconomicEntity(){
		return $this;
	}
	public function initDefaultAccounts(){
		$this->addAccount("Cash", $this->main->getDefaultCash(), $this->main->getMaxCash());
		$this->addAccount("Bank", $this->main->getDefaultBank(), $this->main->getMaxBank());
		foreach($this->main->getBankLoanTypesRaw() as $name => $data){
			$this->addLiability($name, $data["maximum"] * $data["amount"]);
		}
	}
	public function getAbsolutePrefix(){
		return "PocketFactions>>";
	}
	public function isAvailable(){
		return true; // TODO
	}
	public function getRequestableIdentifier(){
		return "PocketFaction " . $this->getID();
	}
	public function canFight(MCEntity $attacker, MCEntity $victim){
		return true;
	}
	public function getMain(){
		return $this->main;
	}
	/**
	 * @param Main $main
	 * @return int The next unique faction ID
	 */
	public static function nextID(Main $main){
		$fid = $main->getCleanSaveConfig()->get("next-fid");
		$main->getCleanSaveConfig()->set("next-fid", $fid + 1);
		$main->getCleanSaveConfig()->save();
		return $fid;
	}
}
