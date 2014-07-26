<?php

namespace pocketfactions\utils;

use pocketfactions\faction\Chunk;
use pocketmine\entity\Entity;
use pocketmine\level\Position;
use pocketmine\Player;

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
	 * @return string
	 */
	public function getDisplayName();
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
	public function canBuild(Player $player, Position $pos);
}
