<?php

namespace Schematic;

use Closure;
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
	 * @return array
	 */
	public static function viewEntries($entries, Closure $singleEntryConverter)
	{
		$result = [];

		foreach ($entries as $entry) {
			$result[] = self::viewEntry($entry, $singleEntryConverter);
		}

		return $result;
	}

}
