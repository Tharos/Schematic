<?php

namespace SchematicTests;

require_once __DIR__ . '/bootstrap.php';

use InvalidArgumentException;
use Schematic\Entries;
use Tester\Assert;
use Tester\TestCase;
use TypeError;


/**
 * @testCase
 */
class EntryTest extends TestCase
{

	/**
	 * @dataProvider provideNotEmptyScalarValues
	 * @dataProvider provideEmptyScalarValues
	 * @dataProvider provideNullValue
	 * @dataProvider provideNotEmptyArray
	 * @dataProvider provideEmptyArray
	 * @dataProvider provideMinimalEntityData
	 */
	public function testGeneralProperty($value)
	{
		$entity = new UniversalProperties([
			'value' => $value,
			'valueRequired' => [],
			'valuesRequired' => [],
		]);

		Assert::same($value, $entity->value);
	}


	/**
	 * @dataProvider provideNotEmptyArray
	 * @dataProvider provideEmptyArray
	 * @dataProvider provideMinimalEntityData
	 */
	public function testRequiredPropertyFromArray($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => $value,
			'valuesRequired' => [],
		]);

		Assert::type(UniversalProperties::class, $entity->valueRequired);
	}


	/**
	 * @dataProvider provideNullValue
	 */
	public function testRequiredPropertyFromNull($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => $value,
			'valuesRequired' => [],
		]);

		Assert::null($entity->valueRequired);
	}


	/**
	 * @dataProvider provideNotEmptyScalarValues
	 * @dataProvider provideEmptyScalarValues
	 */
	public function testRequiredPropertyFromScalar($value)
	{
		Assert::exception(function () use ($value) {
			$entity = new UniversalProperties([
				'value' => null,
				'valueRequired' => $value,
				'valuesRequired' => [],
			]);
			$entity->valueRequired;
		}, TypeError::class);
	}


	/**
	 * @dataProvider provideNotEmptyArray
	 * @dataProvider provideMinimalEntityData
	 */
	public function testNullablePropertyFromNotEmptyArray($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => [],
			'valueNullable' => $value,
			'valuesRequired' => [],
		]);

		Assert::type(UniversalProperties::class, $entity->valueNullable);
	}


	/**
	 * @dataProvider provideNotEmptyScalarValues
	 */
	public function testNullablePropertyFromNotEmptyScalar($value)
	{
		Assert::exception(function () use ($value) {
			$entity = new UniversalProperties([
				'value' => null,
				'valueRequired' => [],
				'valueNullable' => $value,
				'valuesRequired' => [],
			]);
			$entity->valueNullable;
		}, TypeError::class);
	}


	/**
	 * @dataProvider provideEmptyScalarValues
	 * @dataProvider provideNullValue
	 * @dataProvider provideEmptyArray
	 */
	public function testNullablePropertyFromEmptyValue($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => [],
			'valueNullable' => $value,
			'valuesRequired' => [],
		]);

		Assert::null($entity->valueNullable);
	}


	/**
	 * @dataProvider provideNotEmptyArray
	 * @dataProvider provideEmptyArray
	 * @dataProvider provideMinimalEntityData
	 */
	public function testRequiredCollectionFromArray($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => [],
			'valuesRequired' => $value,
		]);

		Assert::type(Entries::class, $entity->valuesRequired);
	}


	/**
	 * @dataProvider provideNullValue
	 */
	public function testRequiredCollectionFromNull($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => [],
			'valuesRequired' => $value,
		]);

		Assert::null($entity->valuesRequired);
	}


	/**
	 * @dataProvider provideNotEmptyScalarValues
	 * @dataProvider provideEmptyScalarValues
	 */
	public function testRequiredCollectionFromScalar($value)
	{
		Assert::exception(function () use ($value) {
			$entity = new UniversalProperties([
				'value' => null,
				'valueRequired' => [],
				'valuesRequired' => $value,
			]);
			$entity->valuesRequired;
		}, TypeError::class);
	}


	/**
	 * @dataProvider provideNotEmptyArray
	 * @dataProvider provideMinimalEntityData
	 */
	public function testNullableCollectionFromNotEmptyArray($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => [],
			'valuesRequired' => [],
			'valuesNullable' => $value,
		]);

		Assert::type(Entries::class, $entity->valuesNullable);
	}


	/**
	 * @dataProvider provideNotEmptyScalarValues
	 */
	public function testNullableCollectionFromNotEmptyScalar($value)
	{
		Assert::exception(function () use ($value) {
			$entity = new UniversalProperties([
				'value' => null,
				'valueRequired' => [],
				'valuesRequired' => [],
				'valuesNullable' => $value,
			]);
			$entity->valuesNullable;
		}, TypeError::class);
	}


	/**
	 * @dataProvider provideEmptyScalarValues
	 * @dataProvider provideNullValue
	 * @dataProvider provideEmptyArray
	 */
	public function testNullableCollectionFromEmptyValue($value)
	{
		$entity = new UniversalProperties([
			'value' => null,
			'valueRequired' => [],
			'valuesRequired' => [],
			'valuesNullable' => $value,
		]);

		Assert::null($entity->valuesNullable);
	}


	public function provideNotEmptyScalarValues()
	{
		return [
			['dummy text'],
			[10],
			[1.5],
			[true],
		];
	}


	public function provideEmptyScalarValues()
	{
		return [
			[''],
			[0],
			[0.0],
			[false],
		];
	}


	public function provideNullValue()
	{
		return [
			[null],
		];
	}


	public function provideNotEmptyArray()
	{
		return [
			[['value-01', 'value-02']],
		];
	}


	public function provideEmptyArray()
	{
		return [
			[[]],
		];
	}


	public function provideMinimalEntityData()
	{
		return [
			[['valueRequired' => 'value', 'valuesRequired' => []]],
		];
	}


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

	/**
	 * @dataProvider provideEmptyScalarValues
	 * @dataProvider provideNullValue
	 * @dataProvider provideEmptyArray
	 */
	public function testEntriesAccessToNullableParameter($customer)
	{
		$order = new Order([
			'customer' => $customer,
			'orderItems' => [],
		]);
		Assert::null($order->customer);
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


	/**
	 * @dataProvider provideDataNullableCustomer
	 */
	public function testEmbeddedEntries($customerId, $customerName)
	{
		$book = new Book([
			'id' => 12,
			'title' => 'PHP: The Bad Parts',
			'tag_name' => 'bestseller',
			'customer_id' => $customerId,
			'customer_name' => $customerName,
			'a_firstname' => 'John',
			'a_surname' => 'Doe',
		]);

		Assert::type(Tag::class, $book->tag);
		Assert::same('bestseller', $book->tag->name);

		Assert::type(Customer::class, $book->customer);
		Assert::same($customerId, $book->customer->id);
		Assert::same($customerName, $book->customer->name);

		Assert::type(Author::class, $book->author);
		Assert::same('John', $book->author->firstname);
		Assert::same('Doe', $book->author->surname);
	}


	public function provideDataNullableCustomer()
	{
		return [
			[20, 'Jack'],
			[null, 'Jack'],
			[20, null],
		];
	}


	public function testNullableEmbeddedEntries()
	{
		$book = new Book([
			'id' => 12,
			'title' => 'PHP: The Bad Parts',
			'tag_name' => 'bestseller',
			'customer_id' => null,
			'customer_name' => null,
			'a_firstname' => 'John',
			'a_surname' => 'Doe',
		]);

		Assert::type(Tag::class, $book->tag);
		Assert::same('bestseller', $book->tag->name);

		Assert::null($book->customer);

		Assert::type(Author::class, $book->author);
		Assert::same('John', $book->author->firstname);
		Assert::same('Doe', $book->author->surname);
	}


	public function testWakeUp()
	{
		$book = unserialize(file_get_contents(__DIR__ . '/serialized.data'));

		Assert::type(Tag::class, $book->tag);
		Assert::same('bestseller', $book->tag->name);

		Assert::type(Customer::class, $book->customer);
		Assert::same(20, $book->customer->id);

		Assert::type(Author::class, $book->author);
		Assert::same('John', $book->author->firstname);
		Assert::same('Doe', $book->author->surname);
	}


	/**
	 * It should allow using isset() and empty() functions to check state of the properties.
	 * No need to assign them to variables before checking their value anymore.
	 */
	public function testIsset()
	{
		$author = new Author([
			'firstname' => 'John',
			'surname' => null,
		]);

		Assert::true(isset($author->firstname));
		Assert::false(empty($author->firstname));

		Assert::false(isset($author->surname));
		Assert::true(empty($author->surname));

		Assert::false(isset($author->id));
		Assert::true(empty($author->id));
	}

}


(new EntryTest)->run();
