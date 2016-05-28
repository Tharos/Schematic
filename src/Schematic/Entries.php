<?php

namespace Schematic;

use Countable;
use Iterator;
use IteratorAggregate;

class Entries implements Countable, IteratorAggregate
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
	}


	/**
	 * @return Entry[]
	 */
	public function toArray()
	{
		return iterator_to_array($this->getIterator());
	}

	/**
	 * @return Iterator
	 */
	public function getIterator()
	{
		reset($this->items);
		$itemType = $this->itemsType;
		foreach ($this->items as $key => $item) {
			if (!array_key_exists($key, $this->cachedItems)) {
				$this->cachedItems[$key] = new $itemType($item);
			}
			yield $key => $this->cachedItems[$key];
		}
	}

	/**
	 * @return int
	 */
	public function count()
	{
		return count($this->items);
	}

}
