<?php

namespace pocketfactions\session;

abstract class PendingOperation{
	protected $op;
	public static $poid = 0;
	public function __construct(callable $onOp){
		$this->op = $onOp;
		$this->id = self::$poid++;
	}
	public function onOp(){
		call_user_func_array($this->op, func_get_args());
	}
	public function getID(){
		return $this->id;
	}
}
