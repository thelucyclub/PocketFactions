<?php

namespace pocketfactions\utils;

use pocketfactions\Main;
use xecon\entity\Service;

class FactServ extends Service{
	public function __construct(Main $main){
		/** @var \xecon\Main $xEcon */
		$xEcon = $main->getServer()->getPluginManager()->getPlugin("xEcon");
		parent::__construct($main, $xEcon);
	}
	public function getName(){
		return "FactServ";
	}
}
