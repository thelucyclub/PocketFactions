<?php

class FactionList{
	public $factions = array();
	public function __construct($path){
		$this->path = $path;
		$this->load();
	}
	public function __destruct(){
		$this->save();
	}
	public function load(){
		
	}
	public function save(){
	}
}
