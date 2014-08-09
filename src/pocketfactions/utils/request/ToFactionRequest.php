<?php

namespace pocketfactions\utils\request;

use legendofmcpe\statscore\request\Request;
use pocketfactions\faction\Faction;

abstract class ToFactionRequest extends Request{
	public function __construct(Faction $to){
		parent::__construct($to);
	}
	public function getFaction(){
		return $this->to;
	}
}
