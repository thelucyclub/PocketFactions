<?php

namespace pocketfactions\requests;

use pocketmine\Player;

class Request{
	public function __construct(Player $from, Player $to, $content, $strId){
		$this->from = $from;
		$this->to= $to;
		$this->content = $content;
		$this->strId = $strId;
	}
	public function getTo(){
		return $this->to;
	}
	public function getStrId(){
		return $this->strId;
	}
	public function getContent(){
		return $this->content;
	}
}
