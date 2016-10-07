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


	public function testEntriesAccess()
	{
		$order = new Order([
			'customer' => [
				'id' => 100,
			],
			'orderItems' => [
				['id' => 1],
				['id' => 2],
			],
		]);

		Assert::type(Entries::class, $order->orderItems);
		Assert::count(2, $order->orderItems);

		Assert::type(Customer::class, $order->customer);
		Assert::same(100, $order->customer->id);
	}


	public function testEntriesClass()
	{
		$order = new Order([
			'orderItems' => [
				[
					'id' => 1,
					'tags' => [
						['name' => 'first-order'],
						['name' => 'premium'],
					],
				],
				[
					'id' => 2,
					'tags' => [],
				],
			],
		], CustomEntries::class);

		Assert::type(CustomEntries::class, $order->orderItems);

		$orderItems = $order->orderItems->toArray();
		/** @var OrderItem $firstOrderItem */
		$firstOrderItem = reset($orderItems);

		Assert::type(CustomEntries::class, $firstOrderItem->tags);
	}


	public function testEmbeddedEntries()
	{
		$book = new Book([
			'id' => 12,
			'title' => 'PHP: The Bad Parts',
			'tag_name' => 'bestseller',
			'customer_id' => 20,
			'a_firstname' => 'John',
			'a_surname' => 'Doe',
		]);

		Assert::type(Tag::class, $book->tag);
		Assert::same('bestseller', $book->tag->name);

		Assert::type(Customer::class, $book->customer);
		Assert::same(20, $book->customer->id);

		Assert::type(Author::class, $book->author);
		Assert::same('John', $book->author->firstname);
		Assert::same('Doe', $book->author->surname);
	}

}


(new EntryTest)->run();
