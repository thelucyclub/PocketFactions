<?php

namespace pocketfactions\session;

use pocketfactions\Main;

use pocketmine\Player;

abstract class Invitation extends PendingOperation{
	public function __construct(callable $op, Player $inviter, $invited, $invitationMessage){
		parent::__construct($op);
		$this->inviter = $inviter->getName();
		$this->invited = $invited;
	}
	public function getInvited(){
		return $this->invited;
	}
	public function getInviter(){
		return $this->inviter;
	}
}
