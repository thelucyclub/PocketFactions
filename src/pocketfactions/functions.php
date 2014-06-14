<?php

namespace pocketfactions;

use pocketmine\utils\TextFormat as Font;

function console($msg){
	Main::get()->getLogger()->info(Font::LIGHT_PURPLE.$msg);
}

function debug($msg, $level = 2){
	Main::get()->getLogger()->debug(Font::GREEN.$msg);
}

function print_var($var, $return = true){
	$info = \print_r($var, true);
	if(is_resource($var)){
		trigger_error("Trying to print variable $var, rejected due to it is a resource");
		return "resource";
	}
	if($return){
		return $info;
	}
	debug(PHP_EOL."  ".str_replace("\n", "\n  ", $info));
	return null;
}
