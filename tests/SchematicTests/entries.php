<?php

namespace SchematicTests;

use Schematic\Entry;


/**
 * @property-read int $id
 */
abstract class Identified extends Entry
{

}


/**
 * @property-read OrderItem[] $orderItems
 * @property-read string $note
 * @property-read bool $approved
 */
class Order extends Identified
{

	protected $associationTypes = [
		'orderItems' => OrderItem::class,
	];

}


class OrderItem extends Identified
{

}
