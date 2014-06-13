<?php

namespace pocketfactions\faction;

use pocketmine\level\Position;

class Chunk{
	/** @var int */
	protected $X, $Z;
	/** @var string */
	protected $level;
	/**
	 * @param int $X
	 * @param int $Z
	 * @param string $level
	 */
	public function __construct($X, $Z, $level){
		$this->X = $X;
		$this->Z = $Z;
		$this->level = $level;
	}
	/**
	 * @return int
	 */
	public function getX(){
		return $this->X;
	}
	/**
	 * @return int
	 */
	public function getZ(){
		return $this->Z;
	}
	/**
	 * @return string
	 */
	public function getLevel(){
		return $this->level;
	}
	/**
	 * @return string
	 */
	public function getWorld(){
		return $this->getLevel();
	}
	/**
	 * @param Chunk $that
	 * @return bool
	 */
	public function equals(static $that){
		return $this->X === $that->X and $this->Z === $that->Z and $this->level = $that->level;
	}
	/**
	 * @param Position $pos
	 * @return static
	 */
	public static function fromObject(Position $pos){
		return new static($pos->getX() >> 4, $pos->getZ() >> 4, $pos->getLevel());
	}
}
