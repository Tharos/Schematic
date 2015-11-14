<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use Schematic\Entries;
use Schematic\Entry;
use Tester\Assert;
use Tester\TestCase;


/**
 * @testCase
 */
class EntriesTest extends TestCase
{

	public function testCurrent()
	{
		$entries = self::createEntries();

		Assert::type(Entry::class, $entries->current());
	}


	public function testKey()
	{
		$entries = self::createEntries();

		Assert::same(5, $entries->key());

		$entries->next();
		Assert::same(6, $entries->key());
	}


	public function testValidity()
	{
		$entries = self::createEntries();

		Assert::true($entries->valid());

		$entries->next();
		Assert::true($entries->valid());

		$entries->next();
		Assert::false($entries->valid());

		$entries->rewind();
		Assert::true($entries->valid());
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
