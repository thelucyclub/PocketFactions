<?php

namespace pocketfactions\tasks;

use pocketfactions\utils\FactionList;
use pocketfactions\Main;
use pocketmine\level\Position;
use pocketmine\scheduler\AsyncTask;

class WriteDatabaseTask extends AsyncTask{
	public function __construct($res){
		$this->res = $res;
	}
	public function onRun(){
		$res = $this->res;
		fwrite($res, FactionList::MAGIC_P);
		fwrite($res, Main::V_CURRENT);
		fwrite($res, Bin::writeInt(count(Main::get()->getFList()->getAll())));
		foreach(Main::get()->getFList()->getAll() as $f){
			fwrite($res, Bin::writeInt($f->getID()));
			fwrite($res, Bin::writeByte(strlen($f->getName()) | ($f->isWhitelisted() ? 0b10000000:0)));
			fwrite($res, $f->getName());
			fwrite($res, Bin::writeShort(strlen($f->getMotto())));
			fwrite($res, $f->getMotto());
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
			fwrite($res, Bin::writeLong($f->getLastActive()));
			$chunks = $f->getChunks();
			array_unshift($chunks, $f->getBaseChunk());
			fwrite($res, Bin::writeShort(count($chunks)));
			foreach($chunks as $c){
				fwrite($res, Bin::writeShort($c->getX()));
				fwrite($res, Bin::writeShort($c->getZ()));
				fwrite($res, Bin::writeByte(strlen($c->getLevel())));
				fwrite($res, $c->getLevel());
			}
//			fwrite($res, Bin::writeInt($f->getHome()->getX() + 2147483648));
//			fwrite($res, Bin::writeShort($f->getHome()->getY()));
//			fwrite($res, Bin::writeInt($f->getHome()->getZ() + 2147483648));
//			fwrite($res, Bin::writeByte(strlen($f->getHome()->getLevel()->getName())));
//			fwrite($res, $f->getHome()->getLevel()->getName());
			$this->writePosition($f->getHome(), $res);
		}
		$states = Main::get()->getFList()->getFactionsStates();
		fwrite($res, Bin::writeLong(count($states)));
		foreach($states as $state){
			fwrite($res, Bin::writeInt($state->getF0()->getID()));
			fwrite($res, Bin::writeInt($state->getF1()->getID()));
			fwrite($res, Bin::writeByte($state->getState()));
		}
		fwrite($res, FactionList::MAGIC_S);
		fclose($res);
	}
	protected function writePosition(Position $pos, $res){
		fwrite($res, Bin::writeInt($pos->getX() >> 4));
		fwrite($res, Bin::writeInt($pos->getZ() >> 4));
		fwrite($res, Bin::writeByte((($pos->getX() & 0x0F) << 4) & ($pos->getZ() & 0x0F)));
		fwrite($res, Bin::writeByte(strlen($pos->getLevel()->getName())));
		fwrite($res, $pos->getLevel()->getName());
	}
}
