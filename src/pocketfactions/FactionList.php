<?php

namespace pocketfactions;

use pocketmine\utils\Binary as Bin;

class FactionList{
	const MAGIC_P = "\0xfa\0xc7\0x10\0x25"; // leet for factions
	const MAGIC_S = "\0xde\0xad\0xc0\0xde"; // leet for deadcode
	public $factions = array();
	public function __construct($path){
		$this->path = $path;
		$this->load();
	}
	public function __destruct(){
		$this->save();
	}
	public function load(){
		
	}
	public function save(){
		$res = fopen("factions.dat", "w");
		$this->saveTo($res);
		fclose($res);
	}
	public function saveTo($res){
		fwrite($res, self::MAGIC_P);
		foreach($this->factions as $f){
			fwrite($res, Bin::writeShort($f->getID());
			fwrite($res, Bin::writeByte(strlen($f->getName()));
			fwrite($res, $f->getName());
			$ranks = $f->getRanks();
			fwrite($res, Bin::writeByte(count($ranks)));
			foreach($ranks as $rk){
				fwrite($res, Bin::writeByte($rk["id"]));
				fwrite($res, Bin::writeByte(strlen($rk["name"])));
				fwrite($res, $rk["name"]);
				fwrite($res, Bin::writeByte($rk["perms"]));
			}
			fwrite($res, Bin::writeByte($f->getDefaultRank()));
			$mbrs = $f->getMembers();
			fwrite($res, Bin::writeShort(count($mbrs)));
			foreach($mbrs as $name=>$rank){
				fwrite($res, strlen($name));
				fwrite($res, $name);
				fwrite($res, Bin::writeByte($rank));
			}
			$chunks = $f->getChunks();
			fwrite($res, Bin::writeByte(count($chunks)));
			foreach($chunks as $c){
				fwrite($c, Bin::writeShort($c->x));
				fwrite($c, Bin::writeShort($c->z));
				fwrite($c, Bin::writeByte(strlen($c->level)));
				fwrite($c, $c->level);
			}
		}
		fwrite($res, self::MAGIC_S);
	}
}
