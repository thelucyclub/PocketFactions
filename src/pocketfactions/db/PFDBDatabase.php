<?php

namespace pocketfactions\db;

use pocketfactions\PocketFactions;

class PFDBDatabase implements Database{
	private $plugin;
	private $nextId = null;
	/** @var array */
	private $opts;
	public function __construct(PocketFactions $plugin, $opts){
		$this->plugin = $plugin;
		$this->opts = $opts;
		$file = $plugin->getDataFolder() . $opts["location"];
		if(!is_file($file)){
			$plugin->getLogger()->notice("Creating an empty database...");
			$src = $plugin->getResource("defaultDatabase.pfdb");
			$target = fopen($file, "wb");
			stream_copy_to_stream($src, $target);
			fclose($src);
			fclose($target);
		}
	}
	public function getPlugin(){
		return $this->plugin;
	}
	/**
	 * @return int
	 */
	public function nextId(){
		if($this->nextId === null){
			throw new \RuntimeException("Database not initialized");
		}
		return $this->nextId++;
	}
	public function loadAll($async = true){
		// TODO: Implement loadAll() method.
	}
	public function saveAll(array $factions, $async = true){
		// TODO: Implement saveAll() method.
	}
}
