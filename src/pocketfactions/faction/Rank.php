<?php

namespace pocketfactions\faction;

class Rank{
	const P_CLAIM			= 0b00000001;
	const P_UNCLAIM			= 0b00000010;
	const P_UNCLAIM_ALL		= 0b00000110;
	const P_SET_WHITE		= 0b00001000;
	const P_ADD_PLAYER		= 0b00010000;
	const P_BUILD			= 0b00100000;
	const P_BUILD_CENTRE	= 0b01000000;
	const P_ALL				= 0xFFFF;
	public function __construct($id, $name, $perms){
		$this->id = $id;
		$this->name = $name;
		$this->perms = $perms;
	}
	public function hasPerm($perm){
		return ($this->perms & $perm) !== 0;
	}
	public function getID(){
		return $this->id;
	}
	public function getPerms(){
		return $this->perm;
	}
	public function setPerm($perm, $bool){
		if(!$bool){
			$this->perm &= ~$perm;
		}
		else{
			$this->perm |= $perm;
		}
	}
	public function getName(){
		return $this->name;
	}
}
