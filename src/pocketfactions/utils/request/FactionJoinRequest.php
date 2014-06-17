<?php

namespace pocketfactions\utils\request;

use pocketfactions\faction\Faction;
use pocketmine\Player;

class FactionJoinRequest extends ToFactionRequest{
	protected $extra;
	protected $from;
	public function __construct(Player $from, Faction $to, $extra = ""){
		parent::__construct($to, $to);
		$this->extra = "";
		$this->from = $from->getName();
	}
	public function getExtraMessage(){
		return $this->extra;
	}
	public function getContent(){
		return $this->from . " sent a request to join your faction.";
	}
	public function onAccepted(){
		// TODO
	}
	public function onRejected(){
		// TODO
	}
}
