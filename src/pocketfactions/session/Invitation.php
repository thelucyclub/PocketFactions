<?php

namespace pocketfactions\session;

abstract class Invitation extends PendingOperation{
	public function __construct(callable $op, Player $inviter, $invited, $invitationMessage){
		parent::__construct($op);
		$this->inviter = $inviter;
		$this->invited = $invited;
		Main::get()->addOfflineMessage($invited, $invitationMessage."\nUse the command \"/poaccept {$this->id}\" to accept this invitation.");
	}
}
