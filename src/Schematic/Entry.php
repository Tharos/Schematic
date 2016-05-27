<?php

namespace Schematic;

use InvalidArgumentException;


class Entry
{

	/**
	 * @var array
	 */
	protected $associationTypes = [];

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

		if (!array_key_exists($name, $this->associationTypes)) {
			return $this->data[$name];

		} else {
			$associationType = $this->associationTypes[$name];

			$this->data[$name] = is_array($associationType) ?
				new Entries($this->data[$name], reset($associationType)) :
				new $associationType($this->data[$name]);

			unset($this->associationTypes[$name]);

			return $this->data[$name];
		}
	}

}
