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
	private $initializedAssociations = [];

	/**
	 * @var array
	 */
	private $data;

	/**
	 * @var string
	 */
	private $entriesClass;


	/**
	 * @param array $data
	 * @param string $entriesClass
	 */
	public function __construct(array $data, $entriesClass = Entries::class)
	{
		if (!is_a($entriesClass, IEntries::class, TRUE)) {
			throw new InvalidArgumentException('Entries class must implement IEntries interface.');
		}

		$this->data = $data;
		$this->entriesClass = $entriesClass;
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

			$entriesClass = $this->entriesClass;

			return $this->data[$name] = is_array($associationType) ?
				new $entriesClass($this->data[$name], reset($associationType)) :
				new $associationType($this->data[$name], $this->entriesClass);
		}
	}

}
