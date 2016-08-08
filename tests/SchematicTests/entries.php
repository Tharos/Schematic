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

	protected $associationTypes = [
		'customer' => Customer::class,
		'orderItems' => [OrderItem::class],
	];

}


/**
 * @property-read Tag[] $tags
 */
class OrderItem extends Identified
{

	protected $associationTypes = [
		'tags' => [Tag::class],
	];

}


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

	protected $associationTypes = [
		'tag' => Tag::class,
		'customer' => Customer::class,
		'author' => Author::class,
	];

	protected $embeddedEntries = [
		'tag' => ['name'],
		'customer.' => ['id'],
		'author.a_' => ['firstname', 'surname'],
	];

}
