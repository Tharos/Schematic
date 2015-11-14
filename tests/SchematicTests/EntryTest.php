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
class EntryTest extends TestCase
{

	public function testFieldAccess()
	{
		$id = 1;

		/** @var IOrder $order */
		$order = new Entry(['id' => $id]);

		Assert::same($id, $order->id);
	}


	public function testInvalidFieldAccess()
	{
		/** @var IOrder $order */
		$order = new Entry([]);

		Assert::exception(function () use ($order) {
			$order->created;
		}, InvalidArgumentException::class, "Missing field 'created'.");
	}


	public function testEntiresAccess()
	{
		/** @var IOrder $order */
		$order = new Entry(['orderItems' => [
			['id' => 1],
			['id' => 2],
		]]);

		Assert::type(Entries::class, $order->orderItems);
		Assert::count(2, iterator_to_array($order->orderItems));
	}

}


(new EntryTest)->run();
