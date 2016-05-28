<?php

namespace Schematic;

use Closure;
use Iterator;
use Traversable;


class EntryViewer
{

	/**
	 * @param mixed $entry
	 * @param Closure $converter
	 * @return mixed
	 */
	public static function viewEntry($entry, Closure $converter)
	{
		return (object) call_user_func($converter, $entry);
	}


	/**
	 * @param array|Traversable $entries
	 * @param Closure $singleEntryConverter
	 * @return Iterator
	 */
	public static function viewEntries($entries, Closure $singleEntryConverter)
	{
		foreach ($entries as $key => $entry) {
			yield $key => self::viewEntry($entry, $singleEntryConverter);
		}
	}

}
