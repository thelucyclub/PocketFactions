<?php

namespace pocketfactions\session;

abstract class PendingOperation{
	protected $op;
	public static $poid = 0;
	public function __construct(callable $onOp){
		$this->op = $op;
		$this->id = self::$poid++;
	}
	public function onOp(){
		call_user_func_array($this->op, func_get_args());
	}
}
