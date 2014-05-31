<?php

namespace pocketfactions;

use pocketmine\event\Listener;

class RequestList implements Listener{
	public function __construct(){
		$this->server = Server::getInstance();
		$this->main = Main::get();
		$this->server->getPluginManager()->registerEvents($this, $this->main);
	}
	public function add(Request $request){
		$this->list[strtolower($request->getTo()->getName())][$request->getStrId()] = $request;
	}
}
