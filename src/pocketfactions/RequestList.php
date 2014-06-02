<?php

namespace pocketfactions;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerCommandPreprocessEvent as PreCmdEvt;
use pocketmine\event\player\PlayerJoinEvent;

class RequestList implements Listener{
	/** @var Request[][] */
	private $list = array();
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
		$p->sendMessage("You have ".count($data)." unprocessed requests.\nUse /req accept <id>, /req decline <id> to accept/decline; use /req read [id] to read the request(s) again."); // unprocessed is not a good verb. Please, English experts (LDX seems to be one), please optimize this sentence.
		$n = 1;
		foreach($data as $k=>$req){
			$p->sendMessage("Request #".($n++)."" .
				": ID $k; Content: ".$req->getContent());
		}
	}
	public function onPreCmd(PreCmdEvt $evt){
		$msg = $evt->getMessage();
		$p = $evt->getPlayer();
		$cmd = strstr($msg, " ", true);
		if(strtolower($cmd) === "/req"){
			$evt->setCancelled(true);
			$cmd = explode(" ", $msg);
			array_shift($cmd);
			$cmd = array_shift($cmd);
			switch($cmd){
			}
		}
	}
}
