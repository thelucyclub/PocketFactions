<?php

namespace pocketfactions;

use pocketmine\event\Listener;
use SimpleAuth\event\PlayerAuthenticateEvent;

class SimpleAuthListener implements Listener{
	private $main;
	public function __construct(Main $plugin){
		$this->main = $plugin;
	}
	public function onAuth(PlayerAuthenticateEvent $event){
		if(Main::$ACTIVITY_DEFINITION === Main::ACTIVITY_AUTH){
			$this->main->onLoggedIn($event->getPlayer());
		}
	}
}
