<?php

namespace pocketfactions;

use pocketmine\utils\TextFormat as Font;

function console($msg, $EOL = true, $debug = 1, $log = true){
	\pocketmine\console(Font::LIGHT_PURPLE."[".Main::NAME."] $msg", $EOL, $log, $debug);
}

function debug($msg, $level = 2){
	\pocketmine\console(Font::GREEN."[".Main::NAME." Debug Info] $msg", true, false, $level);
}

function print_var($var, $return = true){
	$info = \print_r($var, true);
	if(is_resource($var)){
		return false;
	}
	if($return){
		return $info;
	}
	debug(PHP_EOL."  ".str_replace("\n", "\n  ", $info));
}
