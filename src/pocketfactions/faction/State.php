<?php

namespace pocketfactions\faction;

class State{
	const REL_NEUTRAL = 0;
	const REL_TRUCE = 1;
	const REL_ALLY = 2;
	const REL_ENEMY = 3;
	/** @var int */
	private $state;
	/** @var Faction */
	private $f0, $f1;
	public function __construct(Faction $f0, Faction $f1, $state){
		$this->state = $state;
		$this->f0 = $f0;
		$this->f1 = $f1;
	}
	public function getState(){
		return $this->state;
	}
	public function getF0(){
		return $this->f0;
	}
	public function getF1(){
		return $this->f1;
	}
}
