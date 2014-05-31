<?php

namespace pocketfactions;

use pocketmine\Player;

class Request{
	public function __construct(Player $from, Player $to, $name, $strId){
		$this->from = $from;
		$this->to= $to;
		$this->name = $name;
		$this->strId = $strId;
	}
	public function getTo(){
		return $this->to;
	}
	public function getStrId(){
		return $this->strId;
	}
}
