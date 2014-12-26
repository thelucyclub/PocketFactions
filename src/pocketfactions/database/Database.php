<?php

namespace pocketfactions\database;

use pocketfactions\PocketFactions;

abstract class CachedDatabase{
	/** @var PocketFactions */
	private $plugin;

	private $sqlite;
	public function __construct(PocketFactions $plugin){
		$this->plugin = $plugin;
		$this->sqlite = new \SQLite3(":memory:");
		$this->sqlite->exec("
CREATE TABLE factions (
	fid INTEGER UNSIGNED PRIMARY KEY,
	name TEXT COLLATE NOCASE
);
CREATE TABLE factions_players (
	username TEXT PRIMARY KEY,
	fid INTEGER DEFAULT -1,
	rank INTEGER
);
CREATE TABLE factions_chunks (
	x INTEGER,
	z INTEGER,
	fid INTEGER
);
		");
	}
	public abstract function saveFaction();
	public function getFaction($fid){

	}
	public function getFactionByPlayer($name){

	}
	public function getFactionByChunk($X, $Z){

	}
	public function getFactionByCoords($x, $z){
		return $this->getFactionByChunk($x >> 4, $z >> 4);
	}
}
