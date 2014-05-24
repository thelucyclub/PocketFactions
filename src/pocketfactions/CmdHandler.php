<?php

use pocketfactions\utils\PluginCmd as PCmd;
use pocketfactions\tasks\WriteDatabaseTask;
use pocketfactions\tasks\ReadDatabaseTask;

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
							return("Usage: \n/f create <faction-name>");
						}
						
						$maxLength = $this->config->get("max-faction-name-length");
						
						if(strlen($args[0]) > $maxLength){
							return "[PF] The faction name is too long!\n[PF] The faction name must not exceed $maxLength letters.\n";
						}
						
						$fcreate = $this->fcreate($args[0], $issuer->iusername);
						
						//todo
						
						break;
						
					case "invite":
						if(count($args)!=1){
							return("Usage: \n/f invite <target-player>");
						}
						
						$targetp = $this->getValidPlayer($args[0]);
						
						if(!$targetp instanceof Player){
							return("[PF] Invalid Player Name. ");
						}
						break;
					
					case "accept":
					
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
							return("Usage: \n/f setperm <target-player> <rank>");
						}
						$targetp = $this->getValidPlayer($args[0]);
						
						if(!$targetp instanceof Player){
							return("[PF] Invalid Player Name. ");
						}
						
					case "sethome":
						break;
						
					case "home":
						break;
						
					case "money":
						break;
						
					case "quit":
						break;
						
					case "disband":
					
						$fdisband = $this->fdisband($issuer->iusername);
						
							//todo
					
						break;
		}
		
	// getValidPlayer() from xPermsMgr (thx 64ff00 :D) (O_o) 
	
	private function getValidPlayer($username)
	{
		$player = $this->getServer()->getPlayer($username);
		
		return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($username);
	}
	
	public function fcreate(){
	//todo
	}
	
	public function fdisband(){
	//todo
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
				$output .= "/f home - Teleport back to Faction home.\n";
				$output .= "/f money - View Faction Money balance.\n";
				$output .= "/f quit - Quit a Faction.\n";
				$output .= "/f disband - Disband your Faction.\n";
				
				break;
		}
		
		return $output;
	}
}
