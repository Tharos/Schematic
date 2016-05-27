<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use InvalidArgumentException;
use Schematic\Entries;
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

		$order = new Order(['id' => $id]);

		Assert::same($id, $order->id);
	}


	public function testInvalidFieldAccess()
	{
		$order = new Order([]);

		Assert::exception(function () use ($order) {
			$order->created;
		}, InvalidArgumentException::class, "Missing field 'created'.");
	}


	public function testEntiresAccess()
	{
		$order = new Order(['orderItems' => [
			['id' => 1],
			['id' => 2],
		]]);

		Assert::type(Entries::class, $order->orderItems);
		Assert::count(2, $order->orderItems);
	}

}


(new EntryTest)->run();
