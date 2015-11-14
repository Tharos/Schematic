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
	 * @param array $items
	 */
	public function __construct(array $items)
	{
		$this->items = $items;

		$this->rewind();
	}


	/**
	 * @return Entry
	 */
	public function current()
	{
		return new Entry(current($this->items));
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
