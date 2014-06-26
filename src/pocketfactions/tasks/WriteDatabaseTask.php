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
	public function __construct($res, Main $main){
		$this->res = $res;
		$this->main = $main;
		$this->onPreRun();
	}
	public function onPreRun(){
		$res = $this->res;
		$this->writeBuffer($res, FactionList::MAGIC_P);
		$this->writeBuffer($res, Main::V_CURRENT);
		$this->writeBuffer($res, Bin::writeInt(count($this->main->getFList()->getAll())));
		foreach($this->main->getFList()->getAll() as $f){
			if(!($f instanceof Faction))
				continue;
			$this->writeBuffer($res, Bin::writeInt($f->getID()));
			$this->writeBuffer($res, Bin::writeByte(strlen($f->getName()) | ($f->isWhitelisted() ? 0b10000000:0)));
			$this->writeBuffer($res, $f->getName());
			$this->writeBuffer($res, Bin::writeShort(strlen($f->getMotto())));
			$this->writeBuffer($res, $f->getMotto());
			$this->writeBuffer($res, Bin::writeByte(strlen($f->getFounder())));
			$this->writeBuffer($res, $f->getFounder());
			$ranks = $f->getRanks();
			$this->writeBuffer($res, Bin::writeByte(count($ranks)));
			foreach($ranks as $rk){
				$this->writeBuffer($res, Bin::writeByte($rk->getID()));
				$this->writeBuffer($res, Bin::writeByte(strlen($rk->getName())));
				$this->writeBuffer($res, $rk->getName());
				$this->writeBuffer($res, Bin::writeInt($rk->getPerms()));
			}
			$this->writeBuffer($res, Bin::writeByte($f->getDefaultRank()));
			$mbrs = $f->getMembers();
			$this->writeBuffer($res, Bin::writeInt(count($mbrs)));
			foreach($mbrs as $name => $rank){
				$this->writeBuffer($res, Bin::writeByte(strlen($name)));
				$this->writeBuffer($res, $name);
				$this->writeBuffer($res, Bin::writeByte($rank));
			}
			$this->writeBuffer($res, Bin::writeLong($f->getLastActive()));
			$chunks = $f->getChunks();
			array_unshift($chunks, $f->getBaseChunk());
			$this->writeBuffer($res, Bin::writeShort(count($chunks)));
			foreach($chunks as $c){
				$this->writeBuffer($res, Bin::writeShort($c->getX()));
				$this->writeBuffer($res, Bin::writeShort($c->getZ()));
				$this->writeBuffer($res, Bin::writeByte(strlen($c->getLevel())));
				$this->writeBuffer($res, $c->getLevel());
			}
			//			$this->writeBuffer($res, Bin::writeInt($f->getHome()->getX() + 2147483648));
			//			$this->writeBuffer($res, Bin::writeShort($f->getHome()->getY()));
			//			$this->writeBuffer($res, Bin::writeInt($f->getHome()->getZ() + 2147483648));
			//			$this->writeBuffer($res, Bin::writeByte(strlen($f->getHome()->getLevel()->getName())));
			//			$this->writeBuffer($res, $f->getHome()->getLevel()->getName());
			$this->writePosition($f->getHome(), $res);
		}
		$states = $this->main->getFList()->getFactionsStates();
		$this->writeBuffer($res, Bin::writeLong(count($states)));
		foreach($states as $state){
			$this->writeBuffer($res, Bin::writeInt($state->getF0()->getID()));
			$this->writeBuffer($res, Bin::writeInt($state->getF1()->getID()));
			$this->writeBuffer($res, Bin::writeByte($state->getState()));
		}
		$this->writeBuffer($res, FactionList::MAGIC_S);
	}
	protected function writePosition(Position $pos, $res){
		$this->writeBuffer($res, Bin::writeInt($pos->getX() >> 4));
		$this->writeBuffer($res, Bin::writeInt($pos->getZ() >> 4));
		$this->writeBuffer($res, Bin::writeByte((($pos->getX() & 0x0F) << 4) & ($pos->getZ() & 0x0F)));
		$this->writeBuffer($res, Bin::writeByte(strlen($pos->getLevel()->getName())));
		$this->writeBuffer($res, $pos->getLevel()->getName());
	}
	protected function writeBuffer($res, $string){
		$this->buffer .= $string;
	}
	public function onRun(){
		fwrite($this->res, $this->buffer);
		fclose($this->res);
	}
	public function onCompletion(Server $server){
		$this->main->getLogger()->info("PocketFactions database output completed.");
	}
}
