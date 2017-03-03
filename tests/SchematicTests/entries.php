<?php

namespace SchematicTests;

use Schematic\Entries;
use Schematic\Entry;
use Schematic\IEntries;


/**
 * @property-read int $id
 */
abstract class Identified extends Entry
{

}


/**
 * @property-read Customer|NULL $customer
 * @property-read IEntries|OrderItem[] $orderItems
 * @property-read string $note
 * @property-read bool $approved
 */
class Order extends Identified
{

	protected static $associations = [
		'?customer' => Customer::class,
		'orderItems[]' => OrderItem::class,
	];

}


/**
 * @property-read Tag[] $tags
 */
class OrderItem extends Identified
{

	protected static $associations = [
		'?tags[]' => Tag::class,
	];

}

/**
 * @property-read string|null $name
 */
class Customer extends Identified
{

}


/**
 * @property string $name
 */
class Tag extends Entry
{

}


class CustomEntries extends Entries
{

}


/**
 * @property-read string $firstname
 * @property-read string $surname
 */
class Author extends Identified
{

}


/**
 * @property-read Tag $tag
 * @property-read Customer $customer
 * @property-read Author $author
 * @property-read string $title
 */
class Book extends Identified
{

	protected static $associations = [
		'tag.' => Tag::class,
		'?customer.' => Customer::class,
		'author.a_' => Author::class,
	];

}


/**
 * @property-read mixed $value
 * @property-read UniversalProperties $valueRequired
 * @property-read UniversalProperties|null $valueNullable
 * @property-read UniversalProperties[] $valuesRequired
 * @property-read UniversalProperties[]|null $valuesNullable
 */
class UniversalProperties extends Entry
{

	protected static $associations = [
		'valueRequired' => UniversalProperties::class,
		'?valueNullable' => UniversalProperties::class,
		'valuesRequired[]' => UniversalProperties::class,
		'?valuesNullable[]' => UniversalProperties::class,
	];

}
