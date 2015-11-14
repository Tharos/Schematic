<?php

namespace SchematicTests;


/**
 * @property-read int $id
 */
interface Identified
{

}


/**
 * @property-read IOrderItem[] $orderItems
 * @property-read string $note
 * @property-read bool $approved
 */
interface IOrder extends Identified
{

}


interface IOrderItem extends Identified
{

}
