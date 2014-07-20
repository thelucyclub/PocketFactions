<?php

namespace pocketfactions\tasks;



abstract class Bin{
	public static function writeByte($num){
		return chr($num & 0xFF);
	}
	public static function writeShort($num){
		return self::writeBin($num, 2);
	}
	public static function writeInt($int){
		return self::writeBin($int, 4);
	}
	public static function writeLong($int){
		return self::writeBin($int, 8);
	}
	public static function writeBin($int, $digits = null){
		$output = "";
		for($i = (int) floor(log($int, 256)); $i >= 0; $i--){
			$digit = ($int >> (8 * $i)) & 0xFF;
			$output .= chr($digit);
		}
		if(is_int($digits)){
			$output = str_repeat(chr(0x00), $digits).$output;
			$output = substr($output, -$digits);
		}
		return $output;
	}
	public static function readBin($bin){
		$result = 0;
		for($i = 0; $i < strlen($bin); $i++){
			$result = $result << 8;
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
