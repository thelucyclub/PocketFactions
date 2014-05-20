<?php

namespace pocketfactions\tasks;

abstract class Bin{
	public static function writeByte($num){
		return chr($num & 0xFF);
	}
	public static function writeShort($num){
		$num &= 0xFFFF;
		$output = "";
		$output .= chr(($num & 0xFF00) >> 16);
		$output .= chr($num & 0xFF);
		return $output;
	}
	public static function writeInt($int){
		$int &= 0xFFFFFFFF;
		$output = "";
		$output .= chr(($num & 0xFF000000) >> 48);
		$output .= chr(($num & 0x00FF0000) >> 32);
		$output .= chr(($num & 0x0000FF00) >> 16);
		$output .= chr($num & 0x000000FF);
		return $output;
	}
	public static function readBin($bin){
		$result = 0;
		for($i = 0; $i < strlen($bin); $i++){
			$result = $result << 16;
			$result |= ord(substr($bin, $i, 1));
		}
		return $result;
	}
	public static function readShort($bin){
		return self::readBin(substr($bin, -2));
	}
	public static function readInt($bin){
		return self::readBin(substr($bin, -4));
	}
}
