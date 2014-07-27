<?php

namespace pocketfactions\utils;

use pocketfactions\faction\Rank;
use pocketfactions\faction\State;
use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\Main;
use pocketfactions\tasks\ReadDatabaseTask;
use pocketfactions\tasks\WriteDatabaseTask;
use pocketmine\IPlayer;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class FactionList{
	const MAGIC_P = "\x00\x00\xff\xffFACTION-LIST";
	const MAGIC_S = "END-OF-LIST-\xff\xff\x00\x00";
	const WILDERNESS = 0;
	const PVP = 1;
	const SAFE = 2;
	const STAFF = 3;
	/** @var \SQLite3 */
	private $db = null;
	/**
	 * @var bool|Faction[]
	 */
	private $factions = false;
	/**
	 * @var null|AsyncTask
	 */
	public $currentAsyncTask = null;
	public function __construct(Main $main){
		$this->path = $main->getFactionsFilePath();
		$this->server = Server::getInstance();
		$this->main = $main;
		$this->load();
	}
	protected function load($async = true){
		if(!is_file($this->path)){
			$this->factions = [];
			Faction::newInstance("PvP-Zone", "console", [ // console is a banned name
					0 => new Rank(0, "staff", Rank::P_ALL & ~Rank::P_DISBAND, "Staff rank"),
					1 => new Rank(1, "normal", Rank::P_NONE, "Normal players") // cannot disband // TODO fix fighting issue
				], 0, 0, 0, 1, $this->main, [],
				$this->server->getServerName()." server-owned PvP areas", true, self::PVP, PHP_INT_MAX);
			Faction::newInstance("Safe-Zone", "console", [ // console is a banned name
					0 => new Rank(0, "staff", Rank::P_ALL & ~Rank::P_DISBAND, "Staff rank"),
					1 => new Rank(1, "normal", Rank::P_NONE, "Normal players")], // cannot disband
				0, 0, 0, 1, $this->main, [],
				$this->server->getServerName()." server-owned PvP-free areas", true, self::SAFE, PHP_INT_MAX);
			Faction::newInstance("Staffs", "console", [ // console is a banned name
					0 => new Rank(0, "staff", Rank::P_ALL & ~Rank::P_DISBAND, "Staff rank"),
					1 => new Rank(1, "normal", Rank::P_NONE, "Normal players")], // cannot disband
				0, 0, 0, 1, $this->main, [],
				$this->server->getServerName()." staffs", true, self::STAFF, PHP_INT_MAX);
		}
		else{
			$this->loadFrom(fopen($this->path, "rb"), $async);
		}
	}
	/**
	 * @param resource $res
	 * @param bool $async
	 */
	public function loadFrom($res, $async = true){
		if($async){
			$this->scheduleAsyncTask(new ReadDatabaseTask($res, array($this, "setAll"), array($this, "setFactionsStates"), $this->main));
		}
		else{
			new ReadDatabaseTask($res, array($this, "setAll"), array($this, "setFactionsStates"), $this->main, false);
		}
	}
	public function save($async = true){
		$this->saveTo(fopen($this->path, "wb"), $async);
	}
	/**
	 * @param resource $res
	 * @param bool $async
	 */
	public function saveTo($res, $async = true){
		if($async){
			$this->scheduleAsyncTask(new WriteDatabaseTask($res, $this->main));
		}
		else{
			new WriteDatabaseTask($res, $this->main, false);
		}
	}
	/**
	 * @param AsyncTask $asyncTask
	 */
	public function scheduleAsyncTask(AsyncTask $asyncTask){
		if(($this->currentAsyncTask instanceof AsyncTask) and !$this->currentAsyncTask->isFinished()){
			trigger_error("Attempt to schedule an I/O task at Factions database rejected due to another I/O operation at the same resource running");
		}
		$this->server->getScheduler()->scheduleAsyncTask($asyncTask);
	}
	/**
	 * @param Faction[] $factions
	 */
	public function setAll(array $factions){
		$this->factions = [];
		if($this->db instanceof \SQLite3){
			$this->db->close();
			$this->db = null;
		}
		$this->db = new \SQLite3(":memory:");
		$this->db->exec("CREATE TABLE factions (id INT, name TEXT, open INT, lastactive INT);");
		$this->db->exec("CREATE TABLE factions_chunks (x INT, z INT, ownerid INT) WITHOUT ROWID;");
		$this->db->exec("CREATE TABLE factions_rels (smallid INT, largeid INT, relid INT) WITHOUT ROWID;");
		$this->db->exec("CREATE TABLE factions_members (lowname TEXT, factionid INT);");
		$this->db->exec("CREATE TABLE factions_homes (x REAL, y REAL, z REAL, name TEXT, fid INT) WITHOUT WORID;"); // floating point coordinates
		foreach($factions as $f){
			$this->add($f);
		}
	}
	public function add(Faction $faction){
		$this->factions[$faction->getID()] = $faction;
		$op = $this->db->prepare("INSERT INTO factions (id, name, open, lastactive) VALUES (:id, :name, :open, :lastactive);");
		$op->bindValue(":id", $faction->getID());
		$op->bindValue(":name", $faction->getName());
		$op->bindValue(":open", $faction->isOpen() ? 1:0); // maybe we will have more types in the future
		$op->bindValue(":lastactive", $faction->getLastActive());
		$op->execute();
		foreach($faction->getChunks() as $chunk){
			$op = $this->db->prepare("INSERT INTO factions_chunks (x, z, ownerid) VALUES (:x, :z, :id);"); // can we make it faster?
			$op->bindValue(":x", $chunk->getX());
			$op->bindValue(":z", $chunk->getZ());
			$op->bindValue(":id", $faction->getID());
			$op->execute();
		}
		foreach($faction->getMembers() as $member){
			$op = $this->db->prepare("INSERT INTO factions_members (lowname, factionid) VALUES (:lowname, :id);");
			$op->bindValue(":lowname", strtolower($member));
			$op->bindValue(":id", $faction->getID());
			$op->execute();
		}
		foreach($faction->getHomes() as $name => $pos){
			$op = $this->db->prepare("INSERT INTO factions_homes (x, y, z, name, fid) VALUES (:x, :y, :z, :name, :fid);");
			$op->bindValue(":x", $pos->getFloorX());
			$op->bindValue(":y", $pos->getFloorY());
			$op->bindValue(":z", $pos->getFloorZ());
			$op->bindValue(":name", $name);
			$op->bindValue(":fid", $faction->getID());
			$op->execute();
		}
	}
	public function __destruct(){
		$this->db->close();
		$this->save();
	}
	/**
	 * @return bool|Faction[]
	 */
	public function getAll(){
		return $this->factions;
	}
	public function getFactionBySimilarName($name){
		$op = $this->db->prepare("SELECT factionid FROM factions_members WHERE lowname LIKE :lowname;");
		$op->bindValue(":lowname", mb_strtolower($name));
		$result = $op->execute();
		$result = $result->fetchArray(SQLITE3_ASSOC);
		if($result === false){
			return false;
		}
		$id = $result["factionid"];
		return $this->factions[$id];
	}
	/**
	 * @param string|int|IPlayer|Chunk $identifier
	 * @return bool|null|Faction
	 */
	public function getFaction($identifier){
		$id = $this->getFactionID($identifier);
		if(is_int($id)){
			return isset($this->factions[$id]) ? $this->factions[$id]:false;
		}
		return $id;
	}
	/**
	 * @param string|int|IPlayer|Chunk $identifier
	 * @return bool|null|int
	 */
	public function getFactionID($identifier){
		if($this->factions === false or $this->db === null){
			return null;
		}
		switch(true){
			case is_string($identifier): // faction name
				$result = $this->db->query("SELECT id FROM factions WHERE name = '$identifier';");
				$result = $result->fetchArray(SQLITE3_ASSOC);
				if($result === false){
					return false;
				}
				return $result["id"];
			case is_int($identifier): // ID
				return $identifier;
			case $identifier instanceof IPlayer:
				$result = $this->db->query("SELECT factionid FROM factions_members WHERE lowname = '".strtolower($identifier->getName())."';")->fetchArray(SQLITE3_ASSOC);
				if($result === false){
					return false;
				}
				return $result["factionid"];
			case $identifier instanceof Chunk:
				$op = $this->db->prepare("SELECT ownerid FROM factions_chunks WHERE x = :x AND z = :z");
				$op->bindValue(":x", $identifier->getX());
				$op->bindValue(":z", $identifier->getZ());
				$result = $op->execute()->fetchArray(SQLITE3_ASSOC);
				if($result === false){
					return false;
				}
				return $result["ownerid"];
			default:
				return false;
		}
	}
	/**
	 * @param $identifier
	 * @return bool|null|IFaction
	 */
	public function getValidFaction($identifier){
		$f = $this->getFaction($identifier);
		return ($f === false ? $this->main->getWilderness():$f);
	}
	public function disband(Faction $faction){
		$faction->delete();
		unset($this->factions[$faction->getID()]);
		$op = $this->db->prepare("DELETE FROM factions WHERE id = :id;");
		$op->bindValue(":id", $faction->getID());
		$op->execute();
		$op = $this->db->prepare("DELETE FROM factions_chunks WHERE ownerid = :id;");
		$op->bindValue(":id", $faction->getID());
		$op->execute();
		$op = $this->db->prepare("DELETE FROM factions_members WHERE factionid = :id;");
		$op->bindValue(":id", $faction->getID());
		$op->execute();
		$op = $this->db->prepare("DELETE FROM factions_rels WHERE smallid = :id OR largeid = :id;");
		$op->bindValue(":id", $faction->getID());
		$op->execute();
		$op = $this->db->prepare("DELETE FROM factions_homes WHERE fid = :id");
		$op->bindValue(":id", $faction->getID());
		$op->execute();
	}
	/**
	 * @param Faction $f0
	 * @param Faction $f1
	 * @return int
	 */
	public function getFactionsState(Faction $f0, Faction $f1){
		$ids = [$f0->getID(), $f1->getID()];
		$op = $this->db->prepare("SELECT relid FROM factions_rels WHERE smallid = :small AND largeid = :large");
		$op->bindValue(":small", min($ids));
		$op->bindValue(":large", max($ids));
		$result = $op->execute()->fetchArray(SQLITE3_ASSOC);
		return $result === false ? State::REL_NEUTRAL:$result["relid"];
	}
	public function setFactionsState(State $state){
		$op = $this->db->prepare("INSERT OR REPLACE INTO factions_rels (smallid, largeid, relid) VALUES (:min, :max, :state);");
		$op->bindValue(":min", $state->getSmall());
		$op->bindValue(":max", $state->getLarge());
		$op->bindValue(":state", $state->getState());
		$op->execute();
	}
	/**
	 * @param State[] $states
	 */
	public function setFactionsStates(array $states){
		foreach($states as $state){
			$this->setFactionsState($state);
		}
	}
	/**
	 * @return State[]
	 */
	public function getFactionsStates(){
		$out = [];
		$data = $this->db->query("SELECT * FROM factions_rels");
		while(($datum = $data->fetchArray(SQLITE3_ASSOC)) !== false){
			$out[] = new State($this->factions[$datum["smallid"]], $this->factions[$datum["largeid"]], $datum["relid"]);
		}
		return $out;
	}
	/**
	 * @param Chunk $chunk
	 * @return bool|Faction
	 */
	public function isKeyChunk(Chunk $chunk){
		$op = $this->db->prepare("SELECT fid, FROM factions_homes WHERE (x BETWEEN :minx AND :maxx) AND (z BETWEEN :minz AND :maxz);");
		$op->bindValue(":minx", $chunk->getX() * 16);
		$op->bindValue(":maxx", $chunk->getX() * 16 + 15); // it is inclusive BETWEEN right?
		$op->bindValue(":minz", $chunk->getZ() * 16);
		$op->bindValue(":maxz", $chunk->getZ() * 16 + 15);
		$result = $op->execute()->fetchArray(SQLITE3_ASSOC);
		if($result === false){
			return false;
		}
		return $this->factions[$result["fid"]];
	}
	public function onChunkClaimed(Faction $faction, Chunk $chunk){
		$op = $this->db->prepare("INSERT INTO factions_chunks (x, z, ownerid) VALUES (:x, :z, :id);");
		$op->bindValue(":x", $chunk->getX());
		$op->bindValue(":z", $chunk->getZ());
		$op->bindValue(":id", $faction->getID());
		$op->execute();
	}
	public function onChunkUnclaimed(Chunk $chunk){
		$op = $this->db->prepare("DELETE FROM factions_chunks WHERE x = :x AND z = :z;");
		$op->bindValue(":x", $chunk->getX());
		$op->bindValue(":z", $chunk->getZ());
		$op->execute();
		$op = $this->db->prepare("DELETE FROM factions_homes WHERE (x BETWEEN :minx AND :maxx) AND (z BETWEEN :minz AND :maxz);");
		$op->bindValue(":minx", $chunk->getX() * 16);
		$op->bindValue(":maxx", $chunk->getX() * 16 + 15);
		$op->bindValue(":minz", $chunk->getZ() * 16);
		$op->bindValue(":maxz", $chunk->getZ() * 16 + 15);
		$op->execute();
	}
	public function onAllChunksUnclaimed(Faction $faction){
		$op = $this->db->prepare("DETELE FROM factions_chunks WHERE ownerid = :id;");
		$op->bindValue(":id", $faction->getID());
		$op->execute();
		$op = $this->db->prepare("DELETE FROM factions_homes WHERE fid = :id;");
		$op->bindValue(":id", $faction->getID());
		$op->execute();
	}
	public function onMemberJoin(Faction $faction, $name){
		$op = $this->db->prepare("INSERT INTO factions_members (factionid, lowname) VALUES (:id, :name);");
		$op->bindValue(":id", $faction->getID());
		$op->bindValue(":name", strtolower($name));
		$op->execute();
	}
	public function onMemberKick($name){
		$op = $this->db->prepare("DELETE FROM factions_members WHERE lowname = :name;");
		$op->bindValue(":name", strtolower($name));
		$op->execute();
	}
	public function getDb(){
		return $this->db;
	}
}
