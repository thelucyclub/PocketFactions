<?php

namespace pocketfactions\utils\request;

use legendofmcpe\statscore\request\Request;
use pocketfactions\faction\Faction;
use pocketfactions\faction\State;

class RelationModifyRequest extends Request{
	/** @var Faction */
	protected $from;
	/** @var int */
	protected $state;
	/** @var string */
	protected $message;
	public function __construct(Faction $from, Faction $to, $state, $message = ""){
		parent::__construct($to);
		$this->from = $from;
		$this->state = $state;
		$this->message = $message;
	}
	public function getContent(){
		$state = $this->getState();
		$out = $this->from->getDisplayName()." sent a request to your faction to be $state factions.";
		if($this->message){
			$out .= "\nExtra message: ".$this->message;
		}
		return $out;
	}
	public function getState(){
		$state = "neutral";
		if($this->state === State::REL_ALLY){
			$state = "ally";
		}
		if($this->state === State::REL_TRUCE){
			$state = "truce";
		}
		return $state;
	}
	public function onAccepted(){
		/** @var Faction $to */
		$to = $this->getTo();
		$this->from->getMain()->getFList()->setFactionsState(new State($this->from, $to, $this->state));
		$this->from->sendMessage($to->getDisplayName()." accepted the request to be ".$this->getState()." factions.");
		$to->sendMessage("You are now ".$this->getState()." factions with ".$this->from->getDisplayName().".");
	}
	public function onRejected(){
		/** @var Faction $to */
		$to = $this->getTo();
		$this->from->sendMessage("The request to be ".$this->getState()." factions with ".$to->getDisplayName()." has been rejected!");
	}
	public function onRemoved(){
		// TODO
	}
}
