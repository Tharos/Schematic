<?php

namespace Schematic;

use InvalidArgumentException;


class Entry
{

	const INDEX_ENTRYCLASS = 0;
	const INDEX_MULTIPLICITY = 1;
	const INDEX_EMBEDDING = 2;
	const INDEX_NULLABLE = 3;

	/**
	 * @var array
	 */
	protected static $associations = [];

	/**
	 * @var array entryClass => [INDEX_ENTRYCLASS => relatedEntryClass, INDEX_MULTIPLICITY => multiplicity, INDEX_EMBEDDING => embedding, INDEX_NULLABLE => null value allowed]
	 */
	private static $parsedAssociations = [];

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
		if ($entriesClass !== Entries::class && !is_a($entriesClass, IEntries::class, TRUE)) {
			throw new InvalidArgumentException('Entries class must implement IEntries interface.');
		}

		$this->data = $data;
		$this->entriesClass = $entriesClass;

		$this->initParsedAssociations();
	}


	/**
	 * @param string $class
	 */
	private static function parseAssociations($class)
	{
		self::$parsedAssociations[$class] = [];

		foreach (static::$associations as $association => $entryClass) {
			$matches = [];
			$result = preg_match('#^(\?)?([^.[\]]+)(\.[^.[\]]*)?(\[\])?$#', $association, $matches);

			if ($result === 0 || (!empty($matches[3]) && !empty($matches[4]))) {
				throw new InvalidArgumentException('Invalid association definition given: ' . $association);
			}

			self::$parsedAssociations[$class][$matches[2]] = [
				self::INDEX_ENTRYCLASS => $entryClass,
				self::INDEX_MULTIPLICITY => !empty($matches[4]),
				self::INDEX_EMBEDDING => !empty($matches[3]) ?
					($matches[3] === '.' ? $matches[2] . '_' : substr($matches[3], 1)) :
					FALSE,
				self::INDEX_NULLABLE => !empty($matches[1]),
			];
		}
	}


	/**
	 * @param string $name
	 * @return mixed
	 */
	public function __get($name)
	{
		$calledClass = get_called_class();

		if (!isset(self::$parsedAssociations[$calledClass][$name]) || isset($this->initializedAssociations[$name])) {
			return $this->readData($name);
		}

		$this->initializedAssociations[$name] = TRUE;

		$association = self::$parsedAssociations[$calledClass][$name];

		$data = $association[self::INDEX_EMBEDDING] !== FALSE ?
			$this->readEmbeddedEntry($association[self::INDEX_EMBEDDING]) :
			$this->readData($name);

		if ($data === NULL || ($association[self::INDEX_NULLABLE] && static::isEmpty($data))) {
			return $this->data[$name] = NULL;
		}

		$entryClass = $association[self::INDEX_ENTRYCLASS];
		$entriesClass = $this->entriesClass;

		return $this->data[$name] = $association[self::INDEX_MULTIPLICITY] ?
			new $entriesClass($data, $entryClass) :
			new $entryClass($data, $this->entriesClass);
	}


	public function __wakeup()
	{
		$this->initParsedAssociations();
	}


	/**
	 * @param string $name
	 * @return bool
	 */
	public function __isset($name)
	{
		return isset($this->data[$name]);
	}


	/**
	 * @param mixed $value
	 * @return bool
	 */
	protected static function isEmpty($value)
	{
		return empty($value);
	}


	/**
	 * @param string $prefix
	 * @return array|NULL
	 */
	private function readEmbeddedEntry($prefix)
	{
		$values = [];
		$isEmpty = TRUE;
		foreach ($this->data as $field => $value) {
			if (strpos($field, $prefix) !== 0 || strlen($field) <= strlen($prefix)) {
				continue;
			}
			$values[substr($field, strlen($prefix))] = $value;

			if ($value !== NULL) {
				$isEmpty = FALSE;
			}
		}

		return $isEmpty ? NULL : $values; // unfortunately this is still just estimation
	}


	/**
	 * @param string $field
	 * @return mixed
	 */
	private function readData($field)
	{
		if (!array_key_exists($field, $this->data)) {
			throw new InvalidArgumentException("Missing field '$field'.");
		}

		return $this->data[$field];
	}


	private function initParsedAssociations()
	{
		if (!array_key_exists($calledClass = get_called_class(), self::$parsedAssociations)) {
			self::parseAssociations($calledClass);
		}
	}

}
