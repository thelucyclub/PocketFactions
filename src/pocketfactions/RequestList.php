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
	public function onJoin(PlayerJoinEvent $evt){
		$name = strtolower($evt->getPlayer()->getName());
		$data = $this->list[$name];
		$p = $evt->getPlayer();
		$p->sendMessage("You have ".count($data)." unprocessed requests.\nUse /req accept <id>, /req decline <id> to accept/decline; use /req read [id] to read the request(s) again.");
		foreach($data as $k=>$req){
			$n = 1;
			$p->sendMessage("Request #".($n++).: ID $k; Content: ".$req->getContent());
		}
	}
}
