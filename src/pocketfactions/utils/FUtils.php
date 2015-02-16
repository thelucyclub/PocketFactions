<?php

namespace pocketfactions\utils;

class FUtils{
	public static function notNull($msg, ...$values){
		if($msg === null){
			$msg = "Value %d must not be null";
		}
		foreach($values as $i => $value){
			if($value === null){
				throw new \UnexpectedValueException(sprintf($msg, $i + 1));
			}
		}
	}
}
