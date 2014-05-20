<?php

namespace pocketfactions\tasks;

use pocketfactions\FactionList;

use pocketmine\scheduler\AsyncTask;

class WriteDatabaseTask extends AsyncTask{
	public function __construct($res){
		$this->res = $res;
	}
	public function onRun(){
		$res = $this->res;
		fwrite($res, FactionList::MAGIC_P);
		fwrite($res, Main::V_CURRENT);
		fwrite($res, Bin::writeInt(count(Main::get()->getFList()->factions)));
		foreach(Main::get()->getFList()->factions as $f){
			fwrite($res, Bin::writeInt($f->getID());
			fwrite($res, Bin::writeByte(strlen($f->getName())));
			fwrite($res, $f->getName());
			fwrite($res, Bin::writeByte(strlen($f->getFounder())));
			fwrite($res, $f->getFounder());
			$ranks = $f->getRanks();
			fwrite($res, Bin::writeByte(count($ranks)));
			foreach($ranks as $rk){
				fwrite($res, Bin::writeByte($rk->getID()));
				fwrite($res, Bin::writeByte(strlen($rk->getName())));
				fwrite($res, $rk->getName());
				fwrite($res, Bin::writeShort($rk->getPerms()));
			}
			fwrite($res, Bin::writeByte($f->getDefaultRank()));
			$mbrs = $f->getMembers();
			fwrite($res, Bin::writeInt(count($mbrs)));
			foreach($mbrs as $name=>$rank){
				fwrite($res, Bin::writeByte(strlen($name)));
				fwrite($res, $name);
				fwrite($res, Bin::writeByte($rank));
			}
			$chunks = $f->getChunks();
			fwrite($res, Bin::writeShort(count($chunks)));
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
