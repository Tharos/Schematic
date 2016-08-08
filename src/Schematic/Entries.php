<?php

namespace Schematic;

use Iterator;


class Entries implements Iterator, IEntries
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
	 * @var array
	 */
	private $cachedItems = [];


	/**
	 * @param array $items
	 * @param string $itemsType
	 */
	public function __construct(array $items, $itemsType = Entry::class)
	{
		$this->items = $items;
		$this->itemsType = $itemsType;

		$this->rewind();
	}


	/**
	 * @return Entry[]
	 */
	public function toArray()
	{
		return iterator_to_array($this);
	}


	/**
	 * @return Entry
	 */
	public function current()
	{
		$key = $this->key();

		if (array_key_exists($key, $this->cachedItems)) {
			return $this->cachedItems[$key];
		}

		$itemType = $this->itemsType;

		return $this->cachedItems[$key] = new $itemType(current($this->items), get_called_class());
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


	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

}
