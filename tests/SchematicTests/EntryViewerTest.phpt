<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use Schematic\Entries;
use Schematic\EntryViewer;
use Tester\Assert;
use Tester\TestCase;


/**
 * @testCase
 */
class EntryViewerTest extends TestCase
{

	public function testViewEntry()
	{
		$order = new Order([
			'id' => 1,
			'note' => 'please deliver on monday',
			'approved' => TRUE,
			'orderItems' => [
				['id' => 1],
				['id' => 2],
			],
		]);

		$converter = function (Order $order) {
			return [
				'id' => $order->id,
				'note' => $order->note,
				'approved' => $order->approved,
				'orderItems' => EntryViewer::viewEntries($order->orderItems, function (OrderItem $orderItem) {
					return [
						'id' => $orderItem->id,
					];
				}),
			];
		};

		Assert::equal((object ) [
			'id' => 1,
			'note' => 'please deliver on monday',
			'approved' => TRUE,
			'orderItems' => [
				(object) ['id' => 1],
				(object) ['id' => 2],
			],
		], EntryViewer::viewEntry($order, $converter));
	}


	public function testViewEntry_null()
	{
		$order = new Order([
			'id' => 1,
			'note' => 'please deliver on monday',
			'approved' => TRUE,
			'orderItems' => [
				['id' => 1],
				['id' => 2],
			],
		]);

		$converter = function () {
			return NULL;
		};

		Assert::null(EntryViewer::viewEntry($order, $converter));
	}


	public function testViewEntries()
	{
		$customers = new Entries([
			3 => ['id' => 1],
			4 => ['id' => 2],
			5 => ['id' => 3],
		], Customer::class);

		$customers = EntryViewer::viewEntries($customers, function (Customer $customer) {
			return $customer->id === 2 ? NULL : [
				'id' => $customer->id,
			];
		});

		Assert::equal([
			3 => (object) ['id' => 1],
			5 => (object) ['id' => 3],
		], $customers);
	}

}


(new EntryViewerTest)->run();
