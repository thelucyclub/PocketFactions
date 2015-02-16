<?php

namespace pocketfactions\faction\economy;

use pocketfactions\faction\Faction;

interface FactionEconomy{
	public function __construct(Faction $faction);
}
