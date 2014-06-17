<?php
/**
 * Created by PhpStorm.
 * User: 15INCH
 * Date: 14年6月17日
 * Time: 下午4:12
 */
namespace pocketfactions\utils\request;

use legendofmcpe\statscore\Request;
use pocketfactions\faction\Faction;

abstract class ToFactionRequest extends Request{
	public function __construct(Faction $to){
		parent::__construct($to);
	}
	public function getFaction(){
		return $this->to;
	}
}
