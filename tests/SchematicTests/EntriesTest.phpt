<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use Iterator;
use Schematic\Entries;
use Schematic\Entry;
use Tester\Assert;
use Tester\TestCase;


/**
 * @testCase
 */
class EntriesTest extends TestCase
{

	public function testGetIterator()
	{
		$entries = self::createEntries();

		Assert::type(Iterator::class, $entries->getIterator());
		Assert::type(Entry::class, $entries->getIterator()->current());
	}


	public function testCount()
	{
		Assert::count(2, self::createEntries());
	}


	public function testToArray()
	{
		$entries = self::createEntries();

		Assert::type('array', $entries->toArray());
		Assert::count(2, $entries->toArray());
	}


	/**
	 * @return Entries
	 */
	private static function createEntries()
	{
		return new Entries([
			5 => ['id' => 1],
			6 => ['id' => 2],
		]);
	}

}


(new EntriesTest)->run();
