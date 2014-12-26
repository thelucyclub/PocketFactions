<?php

namespace pocketfactions;

use pocketmine\plugin\PluginBase;

class PocketFactions extends PluginBase{
	public function onEnable(){
		$this->saveDefaultConfig();
	}
}
