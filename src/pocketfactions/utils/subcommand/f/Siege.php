<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Chunk;
use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\faction\State;
use pocketfactions\Main;
use pocketmine\math\Vector2;
use pocketmine\Player;

class Siege extends FactionMemberSubcommand{
	public function __construct(Main $main){
		parent::__construct($main, "siege");
	}
	public function getDescription(){
		return "Claim the chunk of a power-overdraft enemy faction you are standing in";
	}
	public function getUsage(){
		return "";
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_SIEGE);
	}
	public function onRun(array $args, Faction $faction, Player $player){
		$chunk = Chunk::fromObject($player);
		$owner = $this->getMain()->getFList()->getFaction($chunk);
		if(!($owner instanceof Faction)){
			return "You cannot siege a wilderness chunk. Consider using \"/f claim\".";
		}
		$state = $this->getMain()->getFList()->getFactionsState($faction, $owner);
		if($state !== State::REL_ENEMY){
			return "Your faction has to declare enemy to faction $owner to siege their chunks.";
		}
		$power = $owner->powerClaimable() - count($owner->getChunks());
		if($power >= 0){
			return "Faction $owner doesn't have power overdraft. You can only siege chunks from factions with overdraft power.";
		}
		$power = ($faction->getPower() - $this->getMain()->getSiegeReputationLoss()) / $this->getMain()->getClaimSingleChunkPower();
		if($power < 0){
			return "Your faction doesn't have enough power to siege a chunk.";
		}
		$x = self::mod($player->getX(), 16);
		$z = self::mod($player->getZ(), 16);
		if((new Vector2($x, $z))->distance(8, 8) >= ($radius = $this->getMain()->getSiegeRadius())){
			return "You must be less than $radius blocks from the chunk center to siege the chunk.";
		}
		$owner->forceUnclaim($chunk);
		$faction->claim($chunk, $player);
		$faction->loseReputation($this->getMain()->getSiegeReputationLoss());
		$owner->sendMessage("You have a chunk sieged by faction $faction!", Faction::CHAT_ANNOUNCEMENT);
		return "Chunk sieged from faction $owner!";
	}
	public static function mod($x, $y){
		while($x < 0){
			$x += $y;
		}
		while($x >= $y){
			$x -= $y;
		}
		return $x;
	}
}
