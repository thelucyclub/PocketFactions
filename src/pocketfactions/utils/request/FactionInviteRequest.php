<?php

namespace pocketfactions\utils\request;

use legendofmcpe\statscore\PlayerRequestable;
use pocketfactions\faction\Faction;
use pocketmine\Server;

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
		// TODO join
		Server::getInstance()->getPlayer($this->getTo()); // the player won't be fast enough to quit the game
		// TODO check
		$this->getFaction()->sendMessage("{$this->getTo()->getName()} is now a member of this faction!");
	}
	public function onRejected(){
		$this->getFaction()->sendMessage("The invitation to {$this->getTo()->getName()} to join this faction has been rejected!");
	}
}
