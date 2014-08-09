<?php

namespace pocketfactions\utils\request;

use legendofmcpe\statscore\request\Request;
use legendofmcpe\statscore\request\Requestable;
use pocketfactions\faction\Faction;

abstract class FromFactionRequest extends Request{
	private $faction;
	public function __construct(Faction $faction, Requestable $to){
		parent::__construct($to);
		$this->faction = $faction;
	}
	public function getFaction(){
		return $this->faction;
	}
}
