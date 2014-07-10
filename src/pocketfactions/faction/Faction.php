<?php

namespace pocketfactions\faction;

use legendofmcpe\statscore\Requestable;
use legendofmcpe\statscore\StatsCore;
use pocketfactions\utils\IFaction;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\entity\Entity as MCEntity;
use pocketmine\inventory\InventoryHolder;
use pocketmine\level\Position;
use pocketmine\Player;
use pocketmine\Server;
use xecon\account\DummyInventory;
use xecon\entity\Entity;

class Faction implements InventoryHolder, Requestable, IFaction{
	use Entity;

	const CHAT_ADMIN = 0;
	const CHAT_ANNOUNCEMENT = 1;
	const CHAT_ALL = 2;
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
	/** @var \pocketmine\level\Position[] $homes */
	protected $homes = [];
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
	 * @param Position|Position[] $home the home position of the faction
	 * @param string $motto
	 * @param bool $whitelist
	 * @param int|bool $id
	 */
	public static function newInstance($name, $founder, array $ranks, $defaultRankIndex, Main $main, $home, $motto = "", $whitelist = true, $id = false){
		if(!is_int($id)){
			$id = self::nextID($main);
		}
		$data = ["name" => $name, "motto" => $motto, "id" => $id, "founder" => strtolower($founder), "ranks" => $ranks, "default-rank" => $ranks[$defaultRankIndex], "members" => [], "last-active" => time(), "chunks" => [], "homes" => (array) $home, "base-chunk" => Chunk::fromObject($home), "whitelist" => $whitelist];
		$faction = new Faction($data, $main);
		$main->getFList()->add($faction);
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
		$this->homes = $args["homes"];
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
	 * @param string $name
	 */
	public function setName($name){
		$this->name = $name;
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
	 * @param bool $raw
	 * @return string[] an array of names of members
	 */
	public function getMembers($raw = false){
		return $raw === false ? array_keys($this->members):$this->members;
	}
	/**
	 * @param int[] $members An array of rank IDs indexed by player names
	 */
	public function setMembers(array $members){
		$this->members = $members;
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
	 * @param Player|string $member
	 * @return Rank
	 */
	public function getMemberRank($member){
		if($member instanceof Player){
			$member = $member->getName();
		}
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
	 * @param string|bool $name name of the home
	 * @return \pocketmine\level\Position|bool
	 */
	public function getHome($name = false){
		if($this->getMain()->getMaxHomes() === 1){
			return array_values($this->homes)[0]; // force order the array in ascending integers and get the first one
		}
		return isset($this->homes[$name]) ? $this->homes[$name]:false;
	}
	/**
	 * @return Position[]
	 */
	public function getHomes(){
		return $this->homes;
	}
	/**
	 * @param string $name
	 * @param Position $pos
	 * @return bool whether the home already exists
	 */
	public function setHome($name = "default", Position $pos){
		$this->homes[$name] = Position::fromObject($pos, $pos->getLevel());
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
	public function canClaimMore(){
		return count($this->chunks) + 1 <= $this->powerClaimable();
	}
	/**
	 * @param Position $pos
	 * @return bool
	 */
	public function isCentreLocation(Position $pos){ // true if a home is here
		foreach($this->getHomes() as $home){
			if($pos->getX() >> 4 === $home->getX() >> 4 and $pos->getX() >> 4 === $home->getX() >> 4){
				return true;
			}
		}
		return false;
	}
	//// Runnable API functions; Command-redirected functions
	/**
	 * @param Player|string $newMember
	 * @param string $method
	 * @return bool|string
	 */
	public function join($newMember, $method){
		if($newMember instanceof Player){
			$newMember = $newMember->getName();
		}
		$this->members[strtolower($newMember)] = $this->getDefaultRank();
		$this->sendMessage("$newMember has joined the faction. Method: $method");
		$this->main->getFList()->onMemberJoin($this, $newMember);
		return true;
	}
	/**
	 * @param string $memberName
	 */
	public function kick($memberName){
		unset($this->members[strtolower($memberName)]);
		$this->main->getFList()->onMemberKick($memberName);
	}
	/**
	 * @param Chunk $chunk
	 * @param Player $player
	 * @return bool|string true on success, or message string on failure
	 */
	public function claim(Chunk $chunk, Player $player){
		if(!$this->canClaimMore()){
			return "Your faction doesn't have enough power to claim more chunks!";
		}
		$charge = $this->main->getChunkClaimFee();
		$account = $this->getAccount($charge["account"]);
		$balance = $account->getAmount() - $charge["amount"];
		if($account->getName() === "Bank"){
			$balance += $this->main->getMaxBankOverdraft();
		}
		if($balance < 0){
			return "Your faction doesn't have money to claim more chunks. Consider donating money to your faction using \"/f donate\".";
		}
		if($balance < $this->main->getMaxBankOverdraft()){
			if(!$this->getMemberRank($player)->hasPerm(Rank::P_SPEND_MONEY_BANK_OVERDRAFT)){
				return Subcommand::NO_PERM;
			}
			$this->sendMessage("[WARNING] The faction's bank account is now overdraf", self::CHAT_ANNOUNCEMENT);
		}
		$this->chunks[] = $chunk;
		$this->main->getFList()->onChunkClaimed($this, $chunk);
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
	public function sendMessage($message, $level = self::CHAT_ADMIN){
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
		return "PocketFactions^^";
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
	public function canBuild(Player $player, Position $pos){
		return $this->getMemberRank($player)->hasPerm(Rank::P_BUILD) and (!$this->isCentreLocation($pos) or $this->getMemberRank($player)->hasPerm(Rank::P_BUILD_CENTRE));
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
