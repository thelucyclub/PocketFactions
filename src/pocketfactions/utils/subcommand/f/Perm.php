<?php

namespace pocketfactions\utils\subcommand\f;

use pocketfactions\faction\Faction;
use pocketfactions\faction\Rank;
use pocketfactions\Main;
use pocketfactions\utils\subcommand\Subcommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;

class Perm extends Subcommand{
	public function __construct(Main $main){
		parent::__construct($main, "perm");
	}
	public function onRun(array $args, Faction $faction, Player $player){
		if(!isset($args[0])){
			return self::WRONG_USE;
		}
		$ranks = $faction->getRanks();
		switch(strtolower(array_shift($args))){
			case "help":
				return str_replace("\r", "", <<<EOH
Usage of /f perm|rank|p|r:
/f perm <app|apoint|assign|promote|demote> <player> <rank> Appoint/promote/demote/assign a player to another rank
/f perm <add|new> <rank name> <permission>,[permission],... [description ...] Add a new rank
/f perm <mod|modify> <rank name> <+/-><permission> [+/-][permission] Modify a rank's permissions
/f perm <desc|description> <rank name> [description ...] Change/view a rank's description
/f perm <rm|remove> <rank name> [rank to reassign members of the removed rank to = default rank] Remove a rank
/f perm rename <old rank name> <new rank name> Rename a rank
/f perm set <rank> <d|default|t|truce|a|ally|s|std|standard>
/f perm list [ranks|perms = ranks] List all ranks in your faction or all permission nodes available.
/f perm claim Claim all permissions you can have in your faction if you are the faction founder.
EOH
);
			case "add":
			case "new":
				if(!isset($args[2])){
					return self::WRONG_USE;
				}
				$name = array_shift($args);
				$permissions = array_shift($args);
				$perms = 0;
				$unknowns = [];
				foreach(preg_split('#,;/\\+#', $permissions, -1, PREG_SPLIT_NO_EMPTY) as $perm){
					$p = self::parsePermission($perm);
					if($p === null){
						$unknowns[] = $perm;
					}
					else{
						$perms |= $p;
					}
				}
				$id = max(array_keys($ranks)) + 1;
				$rank = new Rank($id, $name, $perms, implode(" ", $args));
				$ranks[$id] = $rank;
				$faction->setRanks($ranks);
				return "New rank added: $name.".(count($unknowns) > 0 ?
					("\n[PF] WARNING: The following permission flags are not recognized thus ignored:\n  ".
						implode(", ", $unknowns)):"");
			case "app":
			case "appoint":
			case "assign":
			case "promote":
			case "demote":
				if(!isset($args[1])){
					return self::WRONG_USE;
				}
				$members = $faction->getMembers(true);
				if(!isset($members[$name = array_shift($args)])){
					return "$name isn't in your faction!";
				}
				$orig = $members[$name];
				$newRank = array_shift($args);
				foreach($ranks as $rk){
					if($rk->getName() === $newRank){
						$selected = $rk;
						break;
					}
				}
				if(!isset($selected)){
					return "Rank $newRank not found!";
				}
				$rank = $faction->getMemberRank($player);
				if(!$rank->hasPerm(Rank::P_ALL_PERMS)){
					for($i = 0; $i < 0x40; $i++){
						$perm = pow(2, $i);
						if(!$rank->hasPerm($perm)){
							if($selected->hasPerm($perm) xor $ranks[$orig]->hasPerm($perm)){ // first normal use or XOR ;)
								return "You don't have permission to promote/demote a member to a rank with permission you don't have!"; // thank you freemode ChanServ for this idea :)
							}
						}
					}
				}
				$members[$name] = $selected->getID();
				$faction->setMembers($members);
				return "The rank of $name has been set to $newRank.";
			case "desc":
			case "description":
				if(!isset($args[0])){
					return self::WRONG_USE;
				}
				$name = array_shift($args);
				foreach($faction->getRanks() as $rank){
					if($rank->getName() === $name){
						if(isset($args[0])){
							$rank->setDescription(implode(" ", $args));
							return "Rank description updated.";
						}
						else{
							return "Rank $name: ".$rank->getDescription();
						}
					}
				}
				return "Rank $name not found!";
			case "mod":
			case "modify":
				if(!isset($args[0])){
					return self::WRONG_USE;
				}
				$name = array_shift($args);
				foreach($ranks as $rk){
					if($rk->getName() === $name){
						$rank = $rk;
						break;
					}
				}
				if(!isset($rank)){
					return "Rank $name not found!";
				}
				$unknowns = [];
				$cnt = 0;
				$alreadys = [];
				while(isset($args[0]) and ($args[0]{0} === "+" or $args[0]{0} === "-")){
					$arg = array_shift($args);
					$add = ($arg{0} === "+");
					$arg = substr($arg, 1);
					$node = self::parsePermission($arg);
					if(is_int($node)){
						if($rank->hasPerm($node) xor $add){
							$rank->setPerm($node, $add);
							$cnt++;
						}
						else{
							$alreadys[] = $arg;
						}
					}
					else{
						$unknowns[] = $arg;
					}
				}
				$out = "$cnt permissions of rank $name changed.\n";
				if(count($alreadys)){
					$out .= TextFormat::YELLOW."[PF] The following permissions remain the same:\n[PF] ".implode(", ", $alreadys);
				}
				if(count($unknowns)){
					$out .= TextFormat::RED."[PF] The following unknown permissions are ignored:\n[PF] ".implode(", ", $unknowns);
				}
				return $out;
			case "rm":
			case "remove":
				if(!isset($args[0])){
					return self::WRONG_USE;
				}
				$name = array_shift($args);
				if(isset($args[0])){
					$reassignTo = array_shift($args);
				}
				foreach($ranks as $rk){
					if($rk->getName() === $name){
						$rank = $rk;
					}
					if(isset($reassignTo) and !isset($reassign)){
						if($rk->getName() === $reassignTo){
							$reassign = $rk;
						}
					}
					if(isset($rank) and (!isset($reassignTo) or isset($reassign))){
						break;
					}
				}
				if(!isset($rank)){
					return "Rank $name not found!";
				}
				if(isset($reassignTo) and !isset($reassign)){
					return "Rank $reassignTo not found!";
				}
				if(!isset($reassign)){
					$reassign = $faction->getDefaultRank();
				}
				$members = $faction->getMembers(true);
				$cnt = 0;
				foreach($members as $member => $id){
					if($id === $rank->getID()){
						$members[$member] = $reassign->getID();
						$cnt++;
					}
				}
				$faction->setMembers($members);
				return "Rank $name has been removed. All members ($cnt) of the rank $name have been reassigned to rank ".$reassign->getName().".";
			case "rename":
				if(!isset($args[1])){
					return self::WRONG_USE;
				}
				$old = array_shift($args);
				$new = array_shift($args);
				$rank = $faction->getRankByName($old);
				if(!($rank instanceof Rank)){
					return "Rank $old not found!";
				}
				$rank->setName($new);
				return "Rank $old has been renamed to $new.";
			case "list":
				if(!isset($args[0])){
					array_unshift($args, "ranks");
				}
				switch($list = array_shift($args)){
					case "ranks":
						return "Ranks in your faction: ".implode(", ", array_map(function(Rank $rank){
							return $rank->getName();
						}, $ranks));
					case "perms":
						return "Available permission nodes: ".implode(", ", array_map(function($name){
							return substr($name, 2);
						}, array_keys((new \ReflectionClass("pocketfactions\\faction\\Rank"))->getConstants()))).
								"\nSee https://github.com/LegendOfMCPE/PocketFactions/wiki/permissions for more details.";
					default:
						return "Unknown list: $list";
				}
			case "claim":
				if(strtolower($faction->getFounder()) !== strtolower($player->getName())){
					return self::NO_PERM;
				}
				$id = max(array_keys($ranks));
				$description = "Auto-generated founder rank";
				$i = 2;
				$name = "Founder";
				while($faction->getRankByName($name) instanceof Rank){
					$name = "Founder (".($i++).")";
				}
				$rank = new Rank($id, $name, Rank::P_ALL, $description);
				$ranks[$id] = $rank;
				$faction->setRanks($ranks);
				return "Your rights have been claimed.";
			default:
				return self::WRONG_USE;
		}
	}
	public static function parsePermission($operm){
		$perm = $operm;
		if(substr(strtoupper($perm), 0, 2) !== "P_"){
			$perm = "P_$perm";
		}
		$id = constant("pocketfactions\\faction\\Rank::".strtoupper($perm));
		if(is_int($id)){
			return $id;
		}
		$class = new \ReflectionClass("pocketfactions\\faction\\Rank");
		foreach($class->getConstants() as $name => $id){
			$trimmed = str_replace("_", "", $name);
			if(strtoupper($operm) === $trimmed){
				return $id;
			}
			if(is_numeric($operm) and intval($operm) === $id){
				return $id;
			}
		}
		return null;
	}
	public function checkPermission(Faction $faction, Player $player){
		return $faction->getMemberRank($player)->hasPerm(Rank::P_PERM);
	}
	public function getDescription(){
		return "Manage faction internal permissions";
	}
	public function getUsage(){
		return "<help|cmd>";
	}
	public function getAliases(){
		return ["p", "rank", "ranks", "perms", "r"];
	}
}
