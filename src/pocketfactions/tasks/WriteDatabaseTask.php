<?php

namespace pocketfactions\tasks;

use pocketmine\scheduler\AsyncTask;

class WriteDatabaseTask extends AsyncTask{
	public function __construct($res){
		$this->res = $res;
	}
	public function onRun(){
		$res = $this->res;
		fwrite($res, self::MAGIC_P);
		foreach(Main::get()->getFList()->factions as $f){
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
