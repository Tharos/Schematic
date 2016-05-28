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
	 * @var array
	 */
	private $initializedAssociations = [];


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

		if (!isset($this->associationTypes[$name]) || isset($this->initializedAssociations[$name])) {
			return $this->data[$name];

		} else {
			$this->initializedAssociations[$name] = TRUE;

			$associationType = $this->associationTypes[$name];

			return $this->data[$name] = is_array($associationType) ?
				new Entries($this->data[$name], reset($associationType)) :
				new $associationType($this->data[$name]);
		}
	}

}
