<?php

namespace pocketfactions\io;

interface Buildable{
	/**
	 * @param array $saved saved array from toArray()	
	 * @return static|null a new instance of self, or <code>null</code> if corrupted
	*/
	public static abstract function buildFromSaved($saved);
	public abstract function toRaw();
}
