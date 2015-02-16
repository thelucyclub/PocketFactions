<?php

namespace pocketfactions\db;

interface Database{
	/**
	 * @return \pocketfactions\PocketFactions
	 */
	public function getPlugin();
	/**
	 * @return int
	 */
	public function nextId();
	/**
	 * Load all factions from the database and call PocketFactions::setFactions(Faction[])
	 * @param bool $async default true; whether to load with an asynchrnous task
	 */
	public function loadAll($async = true);
	/**
	 * @param \pocketfactions\faction\Faction[] $factions
	 * @param bool                              $async    default true
	 */
	public function saveAll(array $factions, $async = true);
}
