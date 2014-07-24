<?php

namespace pocketfactions\tasks;

use pocketfactions\faction\Faction;
use pocketfactions\utils\FactionList;
use pocketfactions\Main;
use pocketmine\level\Position;
use pocketmine\scheduler\AsyncTask;
use pocketmine\Server;

class WriteDatabaseTask extends AsyncTask{
	protected $buffer = "";
	public function __construct($res, Main $main, $isAsync = true){
		$this->res = $res;
		$this->main = $main;
		$this->onPreRun();
		if(!$isAsync){
			$this->onRun();
			$this->onCompletion(Server::getInstance());
		}
	}
	public function onPreRun(){
		$this->buffer .= FactionList::MAGIC_P;
		$this->buffer .= Main::V_CURRENT;
		$this->buffer .= Bin::writeInt(count($this->main->getFList()->getAll()));
		foreach($this->main->getFList()->getAll() as $f){
			if(!($f instanceof Faction)){
				continue;
			}
			$this->buffer .= Bin::writeInt($f->getID());
			$this->buffer .= Bin::writeByte(strlen($f->getName()) | ($f->isWhitelisted() ? 0b10000000:0));
			$this->buffer .= $f->getName();
			$this->buffer .= Bin::writeShort(strlen($f->getMotto()));
			$this->buffer .= $f->getMotto();
			$this->buffer .= Bin::writeByte(strlen($f->getFounder()));
			$this->buffer .= $f->getFounder();
			$ranks = $f->getRanks();
			$this->buffer .= Bin::writeByte(count($ranks));
			foreach($ranks as $rk){
				$this->buffer .= Bin::writeByte($rk->getID());
				$this->buffer .= Bin::writeByte(strlen($rk->getName()));
				$this->buffer .= $rk->getName();
				$this->buffer .= Bin::writeLong($rk->getPerms());
			}
			$this->buffer .= Bin::writeByte($f->getDefaultRank()->getID());
			$this->buffer .= Bin::writeByte($f->getAllyRank()->getID());
			$this->buffer .= Bin::writeByte($f->getTruceRank()->getID());
			$mbrs = $f->getMembers();
			$this->buffer .= Bin::writeInt(count($mbrs));
			foreach($mbrs as $name => $rank){
				$this->buffer .= Bin::writeByte(strlen($name));
				$this->buffer .= $name;
				$this->buffer .= Bin::writeByte($rank);
			}
			$this->buffer .= Bin::writeLong($f->getLastActive());
			$this->buffer .= Bin::writeLong($f->getNetReputation());
			$chunks = $f->getChunks();
			$this->buffer .= Bin::writeShort(count($chunks));
			foreach($chunks as $c){
				$this->buffer .= Bin::writeShort($c->getX());
				$this->buffer .= Bin::writeShort($c->getZ());
				$this->buffer .= Bin::writeByte(strlen($c->getLevel()));
				$this->buffer .= $c->getLevel();
			}
			$homes = $f->getHomes();
			$this->buffer .= Bin::writeByte(count($homes));
			foreach($homes as $name => $home){
				$this->buffer .= Bin::writeByte(strlen($name));
				$this->writePosition($home);
			}
		}
		$states = $this->main->getFList()->getFactionsStates();
		$this->buffer .= Bin::writeLong(count($states));
		foreach($states as $state){
			$this->buffer .= Bin::writeInt($state->getF0()->getID());
			$this->buffer .= Bin::writeInt($state->getF1()->getID());
			$this->buffer .= Bin::writeByte($state->getState());
		}
		$this->buffer .= FactionList::MAGIC_S;
	}
	protected function writePosition(Position $pos){
		$this->buffer .= Bin::writeInt($pos->getX() >> 4);
		$this->buffer .= Bin::writeInt($pos->getZ() >> 4);
		$this->buffer .= Bin::writeByte((($pos->getX() & 0x0F) << 4) & ($pos->getZ() & 0x0F));
		$this->buffer .= Bin::writeByte(strlen($pos->getLevel()->getName()));
		$this->buffer .= $pos->getLevel()->getName();
	}
	public function onRun(){
		fwrite($this->res, $this->buffer);
		fclose($this->res);
	}
	public function onCompletion(Server $server){
		$this->main->getLogger()->info("PocketFactions database output completed.");
	}
}
