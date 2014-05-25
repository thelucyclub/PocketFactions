<?php

use pocketfactions\utils\PluginCmd as PCmd;
use pocketfactions\FactionList;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender as Issuer;

class CmdHandler implements CommandExecutor{
	
	public function onCommand(Issuer $issuer, Command $cmd, $lbl, array $args){
		
		switch($cmd->getName()){
			
			case "faction":
				
				if(!($issuer instanceof Player)){
					return PCmd::RUN_IN_GAME;
				}
				
				if(count($args) === 0){
					$args = array("help");
				}
				
				$subcmd = array_shift($args);
				
				switch($subcmd){ // manage subcommand
				
					case "help":
						return $this->help((int) $args[0]);
						
					case "create":
						if(count($args)!=1){
							return("Usage: /f create <faction-name>");
						}
						
						$maxLength = $this->config->get("max-faction-name-length");
						
						if(strlen($args[0]) > $maxLength){
							return "[PF] The faction name is too long!\n[PF] The faction name must not exceed $maxLength letters.\n";
						}
						
						$fcreate = $this->addFaction($args[0], $issuer->iusername);
						
						//todo
						
						break;
						
					case "invite":
						if(count($args)!=1){
							return("Usage: /f invite <target-player>");
						}
						
						$targetp = $this->getValidPlayer($args[0]);
						
						if(!$targetp instanceof Player){
							return("[PF] Invalid Player Name. ");
						}
						
						if($this->usrFaction($issuer->iusername) == false){
							return("[PF] You are not in a member of any faction.");
							}
						if($this->usrFactionPerm($issuer->iusername) != $perm_owner){ // rank check. im not sure what ur going to do. edit this later.
							return("[PF] Only faction owner can do this.");
							}	
						
						//more will be added later.. still thinking - ijoshuahd
						
						break;
					
					case "accept":
						if(count($args) != 0){
							return("Usage: /f accept");
							}
							
						if(isset($this->invFaction[$issuer->iusername]) == false){
							return("[PF] You don't have any invitations.\n[PF] You need to be invited.");
							}
						if($this->usrFaction($issuer->iusername) != false){
							return("[PF] You are already in a faction.");
							}
							
						$tgtFaction = $this->invFaction[$issuer->iusername]["TargetFaction"];
						
						unset($this->invFaction[$issuer->iusername]);
						if($this->existFaction($targetFaction) == false){
							return("[PF] The faction do not exist.\n[PF] Please try to be invited again.");
							}
							
						$joinFac = $this->joinFaction($issuer->username, $targetFaction, $rank); //im not sure about ranks yet. edit this later.
						
							if($joinFac == true){
								return("[PF] You're now a member of " . $tgtFaction . " faction.");
								}else{
									return("[PF] The session has expired/ended.\n[PF] Please try to be invited again.");
										}
										
						break;
						
					case "decline":
					
						break;
						
					case "join":
					
						break;
						
					case "claim":
					
						break;
						
					case "unclaim":
					
						break;
						
					case "unclaimall":
					
						break;
						
					case "kick":
						
						if(count($args) != 1){
							return("Usage: \n/f kick <target-player>");
							
						}
						
						$targetp = $this->getValidPlayer($args[0]);
						
						if(!$targetp instanceof Player){
							return("[PF] Invalid Player Name. ");
						}
						
						break;
						
					case "setperm":
						if(count($args)!=2){
							return("Usage: /f setperm <target-player> <rank>");
						}
						$targetp = $this->getValidPlayer($args[0]);
						
						if(!$targetp instanceof Player){
							return("[PF] Invalid Player Name. ");
						}
						
					case "sethome":
					
						break;
						
					case "setopen":
					
						break;
						
					case "home":
					
						break;
						
					case "money":
					
						break;
						
					case "quit":
					
						break;
						
					case "disband":
					
						$fdisband = $this->rmFaction($issuer->iusername);
						
							//todo
					
						break;
						
					case "motto":
						
						break;
						
		}
		
	// getValidPlayer() from xPermsMgr (thx 64ff00 :D) (O_o) 
	
	private function getValidPlayer($username)
	{
		$player = $this->getServer()->getPlayer($username);
		
		return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($username);
	}
	
	public function help($page){
		
		$page = (1 <= $page and $page <= 3) ? $page : 1;
		
		$output = "";
		
		switch($page){
			
			case 1:
				$output .= "-=[ Pocket Faction Commands (P.1/3) ]=-\n";
				$output .= "/f create - Create a Faction.\n";
				$output .= "/f invite - Invite someone in your Faction.\n";
				$output .= "/f accept - Accept Faction Invitation.\n";
				$output .= "/f decline - Decline Faction Invitation.\n";
				$output .= "/f join - Join public Faction.\n";
				
				break;
				
			case 2:
				$output .= "-=[ Pocket Faction Commands (P.2/3) ]=-\n";
				$output .= "/f claim - Claim areas for your Faction.\n";
				$output .= "/f unclaim - Unclaim areas by your Faction.\n";
				$output .= "/f unclaimall - Unclaim all areas by your Faction.\n";
				$output .= "/f kick - Kick someone in your Faction.\n";
				$output .= "/f setperm - Set permissions in your Faction.\n";
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
