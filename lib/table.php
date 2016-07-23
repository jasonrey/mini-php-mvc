<?php

!defined('SERVER_EXEC') && die('No access.');

abstract class Table
{
	public $tablename;

	// v2.0 - Supports multiple primary key with array
	public $primarykey = 'id';

	public $isNew = true;

	public $db;

	public $activedb = 'default';

	public function __construct()
	{
		$this->db = Lib::db($this->activedb);

		$primarykeys = is_array($this->primarykey) ? $this->primarykey : array($this->primarykey);

		foreach ($primarykeys as $key) {
			$this->$key = null;
		}
	}

	// (array|int|string, int|string...) => bool
	public function load($keys)
	{
		$arguments = func_get_args();
		$totalArgs = func_num_args();

		$primarykeys = $this->getPrimaryKeys();

		if (!is_array($keys)) {
			$primaries = array();

			foreach ($arguments as $index => $value) {
				if (isset($primarykeys[$index])) {
					$primaries[$primarykeys[$index]] = $value;
				}
			}

			$keys = $primaries;
		}

		$sql = 'SELECT * FROM ?? WHERE ';

		$queryValues = array($this->tablename);

		$wheres = array();

		foreach ($keys as $k => $v) {
			$wheres[] = '?? = ?';
			$queryValues[] = $k;
			$queryValues[] = $v;
		}

		$sql .= implode(' AND ', $wheres) . ' LIMIT 1';

		$this->db->query($sql, $queryValues);

		$row = $this->db->fetch();

		if (empty($row)) {
			// If no record found, then prepopulate it with values first
			foreach ($keys as $k => $v) {
				// We don't populate primarykey
				if (in_array($k, $primarykeys)) {
					continue;
				}

				$this->$k = $v;
			}

			$this->error = 'No data found.';

			return false;
		}

		$state = $this->bind($row);

		if ($state === false) {
			return false;
		}

		$this->isNew = false;

		return true;
	}

	// (array|object) => bool
	public function bind($keys, $strict = false)
	{
		if (!is_array($keys) && !is_object($keys)) {
			$this->error = 'Library error: accepted argument is not iteratable.';
			return false;
		}

		$childClass = get_called_class();
		$newObject = new $childClass;
		$allowedKeys = array_keys(get_object_vars($newObject));

		foreach ($keys as $k => $v) {
			if ($strict && !in_array($k, $allowedKeys)) {
				continue;
			}

			$this->$k = $v;
		}

		return true;
	}

	// () => bool
	// Alias to store
	public function save()
	{
		return $this->store();
	}

	// () => bool
	public function store()
	{
		$primarykeys = $this->getPrimaryKeys();

		$childClass = get_called_class();
		$newObject = new $childClass;
		$allowedKeys = array_keys(get_object_vars($newObject));

		// Autopopulate Date
		if (in_array('date', $allowedKeys) && empty($this->date)) {
			$this->date = date('Y-m-d H:i:s');
		}

		// Autopopulate Created
		if (in_array('created', $allowedKeys) && empty($this->created)) {
			$this->created = date('Y-m-d H:i:s');
		}

		// Autopopulate IP
		if (in_array('ip', $allowedKeys) && empty($this->ip)) {
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}

		$disallowedKeys = array('tablename', 'primarykey', 'error', 'id', 'isNew', 'db', 'activedb');

		if ($this->isNew) {
			$sql = 'INSERT INTO ?? ';
			$queryValues = array($this->tablename);

			$columns = array();
			$values = array();

			$count = 0;

			foreach (get_object_vars($this) as $k => $v) {
				if (in_array($k, $disallowedKeys)) {
					continue;
				}

				if (in_array($k, $allowedKeys) && isset($v)) {
					$count++;

					$columns[] = $k;
					$values[] = $v;
				}
			}

			$sql .= '(' . implode(', ', array_fill(0, $count, '??')) . ') VALUES ';
			$sql .= '(' . implode(', ', array_fill(0, $count, '?')) . ')';

			$queryValues = array_merge($queryValues, $columns, $values);

			if (!$this->db->query($sql, $queryValues)) {
				$this->error = $this->db->errorInfo()[2];
				return false;
			}

			$insertId = $this->db->getInsertId();

			if (!empty($insertId) && !empty($primarykeys)) {
				$this->{$primarykeys[0]} = $insertId;
			}

			$this->isNew = false;

			return true;
		} else {
			$sql = 'UPDATE ?? SET ';

			$queryValues = array($this->tablename);

			$sets = array();

			foreach(get_object_vars($this) as $k => $v) {
				if (in_array($k, $disallowedKeys) || !isset($this->$k)) {
					continue;
				}

				if (in_array($k, $allowedKeys)) {
					$sets[] = '?? = ?';

					$queryValues[] = $k;
					$queryValues[] = $v;
				}
			}

			$sql .= implode(', ', $sets) . ' WHERE ';

			$wheres = array();

			foreach ($primarykeys as $pk) {
				if (!isset($this->$pk)) {
					$this->error = 'Library error: Missing ' . $pk . ' primary key value.';
					return false;
				}

				$wheres[] = '?? = ?';
				$queryValues[] = $pk;
				$queryValues[] = $this->$pk;
			}

			$sql .= implode(' AND ', $wheres);

			if (!$this->db->query($sql, $queryValues)) {
				$this->error = $this->db->errorInfo()[2];
				return false;
			}

			return true;
		}
	}

	// () => bool
	public function delete()
	{
		$primarykeys = $this->getPrimaryKeys();

		if (empty($primarykeys)) {
			$this->error = 'Library error: No primary key value.';
			return false;
		}

		$sql = 'DELETE FROM ?? WHERE ';

		$queryValues = array($this->tablename);

		$wheres = array();

		foreach ($primarykeys as $pk) {
			if (!isset($this->$pk)) {
				$this->error = 'Library error: Missing ' . $pk . ' primary key value.';
				return false;
			}

			$wheres[] = '?? = ?';
			$queryValues[] = $pk;
			$queryValues[] = $this->$pk;
		}

		$sql .= implode(' AND ', $wheres);

		if (!$this->db->query($sql, $queryValues)) {
			$this->error = $this->db->errorInfo()[2];
			return false;
		}

		if ($this->primarykey === 'id') {
			$this->id = null;
		}

		$this->isNew = true;

		return true;
	}

	// () => bool
	public function refresh()
	{
		$primarykeys = $this->getPrimaryKeys();

		if (empty($primarykeys)) {
			$this->error = 'Library error: No primary key value.';
			return false;
		}

		$values = array();

		foreach ($primarykeys as $pk) {
			if (!isset($this->$pk)) {
				$this->error = 'Library error: Missing ' . $pk . ' primary key value.';
				return false;
			}

			$values[$pk] = $this->$pk;
		}

		return $this->load($values);
	}

	// (array) => object
	public function export($keys = array())
	{
		$childClass = get_called_class();
		$newObject = new $childClass;
		$allowedKeys = array_keys(get_object_vars($newObject));

		if (!empty($keys)) {
			$allowedKeys = array_intersect($allowedKeys, $keys);
		}

		$disallowedKeys = array('tablename', 'primarykey', 'error', 'isNew', 'db', 'activedb');

		$allowedKeys = array_diff($allowedKeys, $disallowedKeys);

		$obj = new stdClass();

		foreach (get_object_vars($this) as $k => $v) {
			if (!in_array($k, $allowedKeys)) {
				continue;
			}

			$obj->$k = $v;
		}

		return $obj;
	}

	// ($Table) => bool
	public function link($table)
	{
		$classname = get_class($table);

		$primarykeys = $table->getPrimaryKeys();

		if (!empty($primarykeys)) {
			$this->error = 'Table error. No primary keys found in the provided table to link.';
			return false;
		}

		foreach ($primarykeys as $pk) {
			if ($pk === 'id') {
				$keyname = strtolower(str_replace('Table', '', $classname)) . '_' . $pk;

				$this->$keyname = $table->$pk;

				return true;
			}
		}

		$this->error = 'Table error. No ID found in the provided table to link.';

		return false;
	}

	// () => array
	public function getPrimaryKeys()
	{
		if (empty($this->primarykey)) {
			return array();
		}

		return is_array($this->primarykey) ? $this->primarykey : array($this->primarykey);
	}
}
