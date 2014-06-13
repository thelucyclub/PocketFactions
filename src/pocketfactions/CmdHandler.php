<?php

namespace pocketfactions;

use legendofmcpe\statscore\PlayerRequestable;
use legendofmcpe\statscore\StatsCore;
use pocketfactions\faction\Chunk;
use pocketfactions\faction\Rank;
use pocketfactions\faction\Faction;
use pocketfactions\utils\FactionInviteRequest;
use pocketfactions\utils\PluginCmd as PCmd;
use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender as Issuer;
use pocketmine\Player;
use pocketmine\Server;

class CmdHandler implements CommandExecutor{
	public function __construct(){
		$this->config = Main::get()->getCleanSaveConfig();
		$this->main = Main::get();
		$this->server = Server::getInstance();
	}
	public function onCommand(Issuer $issuer, Command $cmd, $lbl, array $args){
		switch(strtolower($cmd->getName())){
			case "faction":
				if(!($issuer instanceof Player)){
					return PCmd::RUN_IN_GAME;
				}
				if(count($args) === 0){
					$args = array("help");
				}
				$subcmd = array_shift($args);
				switch(strtolower($subcmd)){ // manage subcommand
					case "help":
						return $this->help((int) $args[0]);
					case "create":
						if(count($args)!=1){
							return("Usage: /f create <faction-name>");
						}
						$min = $this->config->get("min-faction-name-length");
						$max = $this->config->get("max-faction-name-length");
						if(preg_replace($this->main->getFactionNamingRule(), "", $args[0]) !== ""){
							return "[PF] The faction name is too long!\n".
								"[PF] The faction name must be alphanumeric\n    ".
								"[PF] and optionally with hyphens and underscores\n    ".
								"[PF] in not less than $min characters and not more than $max characters.";
						}
						$id = Faction::nextID();
						$this->main->getFList()->addFaction([
							"name" => $args[0],
							"motto" => "Your Faction Motto. /f motto",
							"id" => $id,
							"founder" => strtolower($issuer->getName()),
							"ranks" => Rank::defaults(),
							"members" => array(strtolower($issuer->getName()) => Rank::defaults()[0]),
							"chunks" => [],
							"base-chunk" => new Chunk((int) $issuer->x / 16, (int) $issuer->z / 16, $issuer->getLevel()->getName()),
							"whitelist" => false,
							"last-active" => time(),
						], $id);
						return "[PF] Faction $args[0] created.";
					case "invite":
						if(!isset($args[0])){
							return "Usage: /f invite <target-player> [extra message ...]";
						}
						$targetp = $this->server->getOfflinePlayer(array_shift($args));
						if(!($targetp instanceof Player)){
							return PCmd::INVALID_PLAYER;
						}
						if($this->main->getFList()->getFaction($targetp) === false){
							return PCmd::NO_FACTION;
						}
						$faction = $this->main->getFList()->getFaction($issuer);
						if($faction->getMemberRank($issuer->getName())->hasPerm(Rank::P_INVITE)){ // rank check. im not sure what ur going to do. edit this later.
							return PCmd::NO_PERM;
						}
						StatsCore::getInstance()->getRequestList()->add($req = new FactionInviteRequest($faction, new PlayerRequestable($targetp), implode(" ", $args)));
						$issuer->sendMessage("The following message has been sent to ".$targetp->getDisplayName().":");
						$issuer->sendMessage("[SENT REQUEST] ".$req->getContent());
						break;
					case "join":
						$fname = array_shift($args);
						$faction = $this->main->getFList()->getFaction($fname);
						if($faction === null){
							return PCmd::DB_LOADING;
						}
						if($faction === false){
							return PCmd::INVALID_FACTION;
						}
						if(!$faction->isOpen()){
							return "[PF] This faction is whitelisted.\n[PF] Please use /req accept <id> if you had been invited."; //why cant you use /f instead? Same thing anyways.
						}
						$success = $faction->join($issuer);
						if($success === true){
							$issuer->sendMessage("You have successfully joined $faction!");
						}
						else{
							$issuer->sendMessage("You cannot join $faction. Reason: $success");
						}
						return null;
					case "claim":
						$f = $this->main->getFList()->getFaction($issuer);
						if($f === null){
							return PCmd::DB_LOADING;
						}
						if($f === false){
							return PCmd::NO_FACTION;
						}
						if(!$f->getMemberRank($issuer->getName())->hasPerm(Rank::P_CLAIM)){
							return PCmd::NO_PERM;
						}
//						$f->claim(Chunk::fromObject($issuer));
						// TODO claim chunk
						break;
					case "unclaim":
						$f = $this->main->getFList()->getFaction($issuer);
						if($f === null){
							return PCmd::DB_LOADING;
						}
						if($f === false){
							return PCmd::NO_FACTION;
						}
						if(!$f->getMemberRank($issuer->getName())->hasPerm(Rank::P_UNCLAIM)){
							return PCmd::NO_PERM;
						}
						// TODO unclaim chunk
						break;
					case "unclaimall":
						$f = $this->main->getFList()->getFaction($issuer);
						if($f === null){
							return PCmd::DB_LOADING;
						}
						if($f === false){
							return PCmd::NO_FACTION;
						}
						if(!$f->getMemberRank($issuer->getName())->hasPerm(Rank::P_UNCLAIM_ALL)){
							return PCmd::NO_PERM;
						}
						// TODO unclaim all chunks except base chunk
						break;
					case "kick":
						if(count($args) != 1){
							return "Usage: \n/f kick <target-player>";
						}
						$targetp = $this->server->getOfflinePlayer(array_shift($args));
						if(!$targetp instanceof Player){
							return PCmd::INVALID_PLAYER;
						}
						$faction = $this->main->getFList()->getFaction($issuer);
						if($faction === null){
							return PCmd::DB_LOADING;
						}
						if($faction === false){
							return PCmd::NO_FACTION;
						}
						if(!$faction->getMemberRank($issuer->getName())->hasPerm(Rank::P_KICK_PLAYER)){
							return PCmd::NO_PERM;
						}
						$targetp->sendMessage("You have been kicked from $faction by ".$issuer->getDisplayName()."!");
						// TODO
						break;
					case "perm":
						$sub = array_shift($args);
						switch($sub){
							case "add":
								break;
							case "remove":
								break;
							case "setplayer":
								break;
							default:
								break;
						}
						break;
					case "sethome":

						break;
					case "setopen":
						$bool = strtolower(array_shift($args));
						if($bool === "true" or $bool === "open" or $bool === "on"){
							$bool = true;
						}
						if($bool === "false" or $bool === "close" or $bool === "closed" or $bool === "not-open" or $bool === "notopen" or $bool === "off"){
							$bool = false;
						}
						if(!is_bool($bool)){
							return false;
						}
						$faction = $this->main->getFList()->getFaction($issuer);
						if($faction === null){
							return PCmd::DB_LOADING;
						}
						if($faction === false){
							return PCmd::NO_FACTION;
						}
						if(!$faction->getMemberRank($issuer->getName())->hasPerm(Rank::P_SET_WHITE)){
							return PCmd::NO_PERM;
						}
						if($faction->isOpen() === $bool){
							return "[PF] Your faction is already ".($bool ? "opened":"closed")."!";
						}
						$faction->setOpen($bool);
						return "[PF] Your faction's open status has been set to ".($bool ? "opened":"closed").".";
					case "home":
						break;
					case "money":
						break;
					case "quit":
						break;
					case "disband":
						break;
					case "motto":
						$faction = $this->main->getFList()->getFaction($issuer);
						if($faction === null){
							return PCmd::DB_LOADING;
						}
						if($faction === false){
							return PCmd::NO_FACTION;
						}
						if(!$faction->getMemberRank($issuer->getName())->hasPerm(Rank::P_SET_MOTTO)){
							return PCmd::NO_PERM;
						}
						$this->main->getFList()->getFaction($issuer)->setMotto(implode(" ", $args));
						return "[PF] Motto set."; // first completed command! :)
				}
				break;
		}
		return true;
	}
	// Server::getOfflinePlayer() returns an online player if possible.
	public function help($page){
		$page = (1 <= $page and $page <= 3) ? $page : 1;
		$output = "";
		switch($page){
			case 1:
				$output .= "-=[ Pocket Faction Commands (P.1/3) ]=-\n";
				$output .= "/f create - Create a Faction.\n";
				$output .= "/f invite - Invite someone in your Faction.\n";
				$output .= "/f join - Join public Faction.\n";
				$output .= "/f accept <invitation id>\n";
				$output .= "/f decline <invitation id>\n";
				break;
			case 2:
				$output .= "-=[ Pocket Faction Commands (P.2/3) ]=-\n";
				$output .= "/f claim - Claim areas for your Faction.\n";
				$output .= "/f unclaim - Unclaim areas by your Faction.\n";
				$output .= "/f unclaimall - Unclaim all areas by your Faction.\n";
				$output .= "/f kick - Kick someone in your Faction.\n";
				$output .= "/f perm - Manage permissions in your Faction.\n";
				$output .= "/f sethome - Set Faction home.\n";
				break;
			case 3:
				$output .= "-=[ Pocket Faction Commands (P.3/3) ]=-\n";
				$output .= "/f setopen - Set Faction available to Public.\n";
				$output .= "/f home - Teleport back to Faction home.\n";
				$output .= "/f money - View Faction Money balance.\n";
				$output .= "/f quit - Quit a Faction.\n";
				$output .= "/f disband - Disband your Faction.\n";
				$output .= "/f motto - Set a faction motto.\n";
				break;
		}
		return $output;
	}
}
