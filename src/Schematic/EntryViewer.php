<?php

namespace Schematic;

use Closure;
use Traversable;


class EntryViewer
{

	/**
	 * @param mixed $entry
	 * @param Closure $converter
	 * @return object|NULL
	 */
	public static function viewEntry($entry, Closure $converter)
	{
		$entry = call_user_func($converter, $entry);

		return $entry !== NULL ? (object) $entry : NULL;
	}


	/**
	 * @param array|Traversable $entries
	 * @param Closure $singleEntryConverter
	 * @return object[]
	 */
	public static function viewEntries($entries, Closure $singleEntryConverter)
	{
		$result = [];

		foreach ($entries as $index => $entry) {
			$entry = self::viewEntry($entry, $singleEntryConverter);

			if ($entry !== NULL) {
				$result[$index] = $entry;
			}
		}

		return $result;
	}

}
