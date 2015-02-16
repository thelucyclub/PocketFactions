<?php

namespace pocketfactions\faction\economy;

interface FactionAccount{
	public function add($amount, $reason = "Unspecified reason");
	public function substract($amount, $reason = "Unspecified reason");
	/**
	 * @param int $amount
	 * @return bool
	 * @throws UnsupportedOperationException
	 */
	public function setMaxAmount($amount);
	/**
	 * @return int
	 */
	public function getMaxAmount();
	/**
	 * @param int $amount
	 * @return int
	 */
	public function setMinAmount($amount);
	/**
	 * @return int
	 */
	public function getMinAmount();
}
