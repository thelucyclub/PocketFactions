<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;

class Sethome extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "sethome");
	}
}
