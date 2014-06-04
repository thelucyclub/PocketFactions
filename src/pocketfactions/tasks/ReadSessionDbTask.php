<?php
/**
 * Created by PhpStorm.
 * User: 15INCH
 * Date: 14年6月4日
 * Time: 下午5:09
 */

namespace pocketfactions\tasks;

use pocketmine\scheduler\AsyncTask;

class ReadSessionDbTask extends AsyncTask{
	/** @var callable */
	private $callback;
	/** @var resource */
	private $res;
	public function __construct($res, callable $callback){
		$this->callback = $callback;
		$this->res = $res;
	}
	public function onRun(){

	}
}
