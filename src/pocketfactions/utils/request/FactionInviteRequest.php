<?php

namespace pocketfactions\utils\request;

use legendofmcpe\statscore\request\PlayerRequestable;
use pocketfactions\faction\Faction;

class FactionInviteRequest extends FromFactionRequest{
	protected $extra;
	public function __construct(Faction $from, PlayerRequestable $to, $extra = ""){
		parent::__construct($from, $to);
		$this->extra = $extra;
	}
	public function getExtraMessage(){
		return $this->extra;
	}
	public function getContent(){
		return "You have received an invitation to join faction " . $this->getFaction()->getName() . ". {$this->getExtraMessage()}";
	}
	public function onAccepted(){
		$this->getFaction()->sendMessage("The invitation to {$this->getTo()->getName()} to join this faction has been accepted!");
		$player = $this->getFaction()->getMain()->getServer()->getPlayer($this->getTo()); // the player won't be fast enough to quit the game
		$this->getFaction()->join($player, "Invitation");
		$this->getFaction()->sendMessage("{$this->getTo()->getName()} is now a member of this faction!");
	}
	public function onRejected(){
		$this->getFaction()->sendMessage("The invitation to {$this->getTo()->getName()} to join this faction has been rejected!");
	}
	public function onRemoved(){
		// TODO
	}
}
