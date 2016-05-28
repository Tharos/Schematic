<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use Iterator;
use Schematic\Entries;
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
			'orderItems' => [],
		]);

		$converter = function (Order $order) {
			return [
				'id' => $order->id,
				'note' => $order->note,
				'approved' => $order->approved,
				'orderItems' => $order->orderItems->toArray(),
			];
		};

		Assert::equal((object ) [
			'id' => 1,
			'note' => 'please deliver on monday',
			'approved' => TRUE,
			'orderItems' => [],
		], EntryViewer::viewEntry($order, $converter));
	}

    public function testViewEntriesTwice()
    {
	    $orderItems = new Entries([
		    ['id' => 1],
		    ['id' => 2],
	    ], OrderItem::class);

	    $converter = function (OrderItem $orderItem) {
		    return [
			    'id' => $orderItem->id
		    ];
	    };

	    $entries = EntryViewer::viewEntries($orderItems, $converter);
	    Assert::type(Iterator::class, $entries);
	    Assert::equal(
		    (object) ['id' => 1],
		    $entries->current()
	    );
	    $entries->next();
	    Assert::equal(
		    (object) ['id' => 2],
		    $entries->current()
	    );

	    $entries = EntryViewer::viewEntries($orderItems, $converter);
	    Assert::type(Iterator::class, $entries);
	    Assert::equal(
		    (object) ['id' => 1],
		    $entries->current()
	    );
	    $entries->next();
	    Assert::equal(
		    (object) ['id' => 2],
		    $entries->current()
	    );
    }

}


(new EntryViewerTest)->run();
