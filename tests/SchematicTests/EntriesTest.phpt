<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use InvalidArgumentException;
use Schematic\Entries;
use Schematic\Entry;
use Tester\Assert;
use Tester\TestCase;


/**
 * @testCase
 */
class EntriesTest extends TestCase
{

	/**
	 * @return Entries
	 */
	private static function createEntries()
	{
		return new Entries([
			5 => ['id' => 1],
			6 => ['id' => 2],
			7 => ['id' => 3],
		]);
	}


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
		Assert::true($entries->valid());

		$entries->next();
		Assert::false($entries->valid());

		$entries->rewind();
		Assert::true($entries->valid());
	}


	public function testCount()
	{
		Assert::count(3, self::createEntries());
	}


	public function testToArray()
	{
		$entries = self::createEntries();

		Assert::type('array', $entries->toArray());
		Assert::count(3, $entries->toArray());
	}


	public function testHas()
	{
		$entries = self::createEntries();

		Assert::true($entries->has(5));
		Assert::true($entries->has(6));

		Assert::false($entries->has(8));
		Assert::false($entries->has(1));
	}


	public function testGet()
	{
		$entries = self::createEntries();

		Assert::equal(new Entry(['id' => 1]), $entries->get(5));
		Assert::equal(new Entry(['id' => 2]), $entries->get(6));

		Assert::exception(function () use ($entries) {
			$entries->get(8);
		}, InvalidArgumentException::class, 'Missing entry with key 8.');
	}


	public function testRemove()
	{
		$entries = self::createEntries();

		Assert::true($entries->has(5));

		$entries = $entries->remove(5);

		Assert::false($entries->has(5));

		$entries = self::createEntries();

		Assert::true($entries->has(5));
		Assert::true($entries->has(6));

		$entries = $entries->remove(5, 6);

		Assert::false($entries->has(5));
		Assert::false($entries->has(6));

		$entries = self::createEntries();

		Assert::true($entries->has(5));
		Assert::true($entries->has(6));

		$entries = $entries->remove(...[5, 6]);

		Assert::false($entries->has(5));
		Assert::false($entries->has(6));

		$entries = self::createEntries();

		Assert::exception(function () use ($entries) {
			$entries->remove(...[1, 2, 5]);
		}, InvalidArgumentException::class, 'Missing entries with keys: 1, 2.');
	}


	public function testReduceTo()
	{
		$entries = self::createEntries();

		Assert::true($entries->has(5));
		Assert::true($entries->has(6));

		$entries = $entries->reduceTo(6);

		Assert::false($entries->has(5));
		Assert::true($entries->has(6));

		$entries = self::createEntries();

		$entries = $entries->reduceTo(6, 7);

		Assert::false($entries->has(5));
		Assert::true($entries->has(6));
		Assert::true($entries->has(7));

		$entries = self::createEntries();

		$entries = $entries->reduceTo(...[6, 7]);

		Assert::false($entries->has(5));
		Assert::true($entries->has(6));
		Assert::true($entries->has(7));

		$entries = self::createEntries();

		Assert::exception(function () use ($entries) {
			$entries->reduceTo(...[1, 2, 5]);
		}, InvalidArgumentException::class, 'Missing entries with keys: 1, 2.');
	}


	public function testTransform()
	{
		$entries = self::createEntries();

		Assert::true($entries->has(5));
		Assert::false($entries->has(8));

		$entries = $entries->transform(function (array $data) {
			unset($data[5]);
			$data[8] = ['id' => 3];

			return $data;
		});

		Assert::false($entries->has(5));
		Assert::true($entries->has(8));
	}

}


(new EntriesTest)->run();
