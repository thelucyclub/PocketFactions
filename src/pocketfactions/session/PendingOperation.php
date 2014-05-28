<?php

abstract class PendingOperation{
	protected $op;
	public function __construct(callable $onOp){
		$this->op = $op;
	}
	public function onOp(){
		call_user_func_array($this->op, func_get_args());
	}
}
