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
	protected $embeddedEntries = [];

	/**
	 * @var array
	 */
	private $initializedAssociations = [];

	/**
	 * @var array
	 */
	private $embeddedEntriesIndex = [];

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

		$this->buildEmbeddedEntriesIndex();
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		if (!isset($this->associationTypes[$name]) || isset($this->initializedAssociations[$name])) {
			return $this->readData($name);
		}

		$this->initializedAssociations[$name] = TRUE;

		$data = isset($this->embeddedEntriesIndex[$name]) ? $this->readEmbeddedEntry($name) : $this->readData($name);

		if ($data === NULL) {
			return $this->data[$name] = NULL;
		}

		$associationType = $this->associationTypes[$name];
		$entriesClass = $this->entriesClass;

		return $this->data[$name] = is_array($associationType) ?
			new $entriesClass($data, reset($associationType)) :
			new $associationType($data, $this->entriesClass);
	}


	private function buildEmbeddedEntriesIndex()
	{
		foreach ($this->embeddedEntries as $name => $fields) {
			$nameWithoutPeriod = $name;
			$prefix = '';

			$periodPosition = strpos($name, '.');
			if ($periodPosition !== FALSE) {
				$nameWithoutPeriod = substr($name, 0, $periodPosition);
				$prefix = substr($name, $periodPosition + 1);

				if ($prefix === FALSE) { // trailing period
					$prefix = $nameWithoutPeriod . '_';
				}
			}

			$this->embeddedEntriesIndex[$nameWithoutPeriod] = [];
			foreach ($fields as $field) {
				$this->embeddedEntriesIndex[$nameWithoutPeriod][$field] = $prefix . $field;
			}
		}
	}


	/**
	 * @param string $name
	 * @return array|NULL
	 */
	private function readEmbeddedEntry($name)
	{
		$values = [];
		$isEmpty = TRUE;
		foreach ($this->embeddedEntriesIndex[$name] as $field => $prefixedField) {
			$values[$field] = $this->readData($prefixedField);

			if ($values[$field] !== NULL) {
				$isEmpty = FALSE;
			}
		}

		return $isEmpty ? NULL : $values; // unfortunately this is still just estimation
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	private function readData($name)
	{
		if (!array_key_exists($name, $this->data)) {
			throw new InvalidArgumentException("Missing field '$name'.");
		}

		return $this->data[$name];
	}

}
