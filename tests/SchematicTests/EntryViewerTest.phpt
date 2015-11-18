<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use Schematic\EntryViewer;
use Tester\Assert;
use Tester\TestCase;


/**
 * @testCase
 */
class EntryViewerTest extends TestCase
{

	public function testView()
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
						'id' => $orderItem->id
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

}


(new EntryViewerTest)->run();
