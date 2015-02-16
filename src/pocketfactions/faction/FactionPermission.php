<?php

namespace pocketfactions\faction;

class FactionPermission{ // I WANT AN ENUM! SHOGHICP, WHY DON'T YOU USE JAVA!
	const PERM_CLAIM = 0b00000000000000000000000000000000;
	public $tier;
	public $bitmask;
	public static function fromString($string, &$tier){
		switch($string){
			// TODO
		}
		$tier = -1;
		return -1;
	}
}
