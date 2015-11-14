<?php

namespace Schematic;

use Iterator;


class Entries implements Iterator
{

	/**
	 * @var array
	 */
	private $items;

	/**
	 * @var string
	 */
	private $itemsType;


	/**
	 * @param array $items
	 * @param string|NULL $itemsType
	 */
	public function __construct(array $items, $itemsType = NULL)
	{
		$this->items = $items;
		$this->itemsType = $itemsType !== NULL ? (string) $itemsType : Entry::class;

		$this->rewind();
	}


	/**
	 * @return Entry
	 */
	public function current()
	{
		$itemType = $this->itemsType;

		return new $itemType(current($this->items));
	}


	public function next()
	{
		next($this->items);
	}


	/**
	 * @return mixed
	 */
	public function key()
	{
		return key($this->items);
	}


	/**
	 * @return bool
	 */
	public function valid()
	{
		return array_key_exists(key($this->items), $this->items);
	}


	public function rewind()
	{
		reset($this->items);
	}

}
