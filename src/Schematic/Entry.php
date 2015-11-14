<?php

namespace Schematic;

use InvalidArgumentException;


class Entry
{

	/**
	 * @var array
	 */
	private $data;


	/**
	 * @param array $data
	 */
	public function __construct(array $data)
	{
		$this->data = $data;
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (!array_key_exists($name, $this->data)) {
			throw new InvalidArgumentException("Missing field '$name'.");
		}

		return is_array($this->data[$name])
			? new Entries($this->data[$name])
			: $this->data[$name];
	}

}
