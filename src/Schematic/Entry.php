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

		if (is_array($this->data[$name])) {
			$associationType = NULL;
			if (array_key_exists($name, $this->associationTypes)) {
				$associationType = $this->associationTypes[$name];
			}
			return new Entries($this->data[$name], $associationType);

		} else {
			return $this->data[$name];
		}
	}

}
