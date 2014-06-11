<?php

namespace pocketfactions\faction;

use legendofmcpe\statscore\Requestable;
use pocketfactions\Main;
use pocketmine\inventory\InventoryHolder;
use pocketmine\Player;
use pocketmine\Server;
use xecon\account\DummyInventory;
use xecon\entity\Entity;

class Faction implements InventoryHolder, Requestable{
	use Entity;
	/**
	 * @var string $name
	 */
	protected $name;
	/**
	 * @var string $motto
	 */
	protected $motto;
	/**
	 * @var int $id
	 */
	protected $id;
	/**
	 * @var string $founder
	 */
	protected $founder;
	/**
	 * @var Rank[] $ranks indexed by internal rank IDs
	 */
	protected $ranks;
	/**
	 * @var int $defaultRank internal rank ID of the default rank
	 */
	protected $defaultRank;
	/**
	 * This array is a list keyed with lowercase member names and filled with Rank object references to $Faction->ranks.
	 * Use <code>array_keys()</code> to get a plain list of members.
	 * @var Rank[] $members
	 */
	protected $members;
	/**
	 * @var  int $lastActive
	 */
	protected $lastActive;
	/**
	 * @var Chunk[] $chunks numerically keyed chunks with undefined order (possibly sequence of claiming)
	 */
	protected $chunks;
	/**
	 * @var Chunk $baseChunk The base chunk o a faction. TODO should we remove it? Possibly yes.
	 */
	protected $baseChunk;
	/**
	 * @var \pocketmine\level\Position $home
	 */
	protected $home;
	/**
	 * @var bool
	 */
	protected $whitelist;
	/**
	 * @var FactionEntity
	 */
	protected $econEnt;
	/**
	 * @var Server
	 */
	public $server;
	public function __construct(array $args){
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
		$this->econEnt = new FactionEntity($this);
		$this->server = Server::getInstance();
		$levels = [];
		foreach($this->chunks as $chunk){
			$level = $chunk->getLevel();
			if(!isset($levels[$level])){
				$levels[] = $level;
				if(!$this->server->isLevelLoaded($level)){
					if(!$this->server->isLevelGenerated($this->server->loadLevel($level))){
						$this->server->generateLevel($level, Main::get()->getUserConfig()->get("seed"));
					}
					$this->server->loadLevel($level);
				}
			}
		}
	}
	public function getEconomicEntity(){
		return $this;
	}
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
	/**
	 * @param Player $newMember
	 * @return bool $success
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
	public function hasChunk(Chunk $chunk){
		foreach($this->chunks as $chunk){
			if($chunk->equals($chunk)){
				return true;
			}
		}
		return false;
	}
	public function powerClaimable(){
		$power = $this->getPower();
	}
	public function getPower(){
		foreach($this->members as $mbr){
			$data = Main::get()->getPlayerDb();

		}
	}
	public function getInventory(){
		return new DummyInventory($this, "Faction Account"); // TODO replace the dummy placeholder
	}
	public function __toString(){
		return $this->getName();
	}
	public function sendMessage($message){

		return null;
	}
	public function initDefaultAccounts(){
		$this->addAccount("Cash", 500);
	}
	public function getAbsolutePrefix(){
		return "PocketFactions>>";
	}
	public function isAvailable(){
		return true; // TODO
	}
	/**
	 * @return int The next unique faction ID
	 */
	public static function nextID(){
		$fid = Main::get()->getConfig()->get("next-fid");
		Main::get()->getConfig()->set("next-fid", $fid + 1);
		Main::get()->getConfig()->save();
		return $fid;
    }
}
