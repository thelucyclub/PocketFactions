<?php

namespace pocketfactions\utils;

use pocketfactions\faction\Chunk;
use pocketmine\entity\Entity;

interface IFaction{
	/**
	 * @return int
	 */
	public function getID();
	/**
	 * @return string
	 */
	public function getName();
	/**
	 * @param Chunk $chunk
	 * @return bool
	 */
	public function hasChunk(Chunk $chunk);
	/**
	 * @param string $name
	 * @return bool
	 */
	public function hasMember($name);
	/**
	 * @return bool
	 */
	public function isOpen();
	/**
	 * @param string $member
	 * @return \pocketfactions\faction\Rank|bool
	 */
	public function getMemberRank($member);
	public function canFight(Entity $entity, Entity $entity);
	public function getMain();
}
