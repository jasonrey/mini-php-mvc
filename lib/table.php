<?php

!defined('SERVER_EXEC') && die('No access.');

abstract class Table
{
	public $tablename;

	public $primarykey = 'id';

	public $isNew = true;

	public $db;

	public $activedb = 'default';

	public function __construct()
	{
		$this->db = Lib::db($this->activedb);

		$this->{$this->primarykey} = null;
	}

	// array/int -> bool
	public function load($keys)
	{
		if (!is_array($keys)) {
			$keys = array($this->primarykey => $keys);
		}

		$sql = 'SELECT * FROM `' . $this->tablename . '` WHERE ';

		$wheres = array();

		foreach ($keys as $k => $v) {
			$wheres[] = '`' . $k . '` = ' . $this->db->quote($v);
		}

		$sql .= implode(' AND ', $wheres) . ' LIMIT 1';

		$result = $this->db->query($sql);

		if ($result->num_rows === 0) {
			$this->error = $this->db->error;
			return false;
		}

		$row = $result->fetch_object();

		$state = $this->bind($row);

		if (!$state) {
			// If no record found, then prepopulate it with values first
			foreach ($keys as $k => $v) {
				// We don't populate primarykey
				if ($k === $this->primarykey) {
					continue;
				}

				$this->$k = $v;
			}

			return false;
		}

		$this->isNew = false;

		return true;
	}

	// array/object -> bool
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

	// -> bool
	// Alias to store
	public function save()
	{
		return $this->store();
	}

	// -> bool
	public function store()
	{
		$childClass = get_called_class();
		$newObject = new $childClass;
		$allowedKeys = array_keys(get_object_vars($newObject));

		// Autopopulate Date
		if (in_array('date', $allowedKeys) && empty($this->date)) {
			$this->date = date('Y-m-d H:i:s');
		}

		// Autopopulate IP
		if (in_array('ip', $allowedKeys) && empty($this->ip)) {
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}

		$disallowedKeys = array('tablename', 'primarykey', 'error', 'id', 'isNew', 'db', 'activedb');

		if ($this->isNew) {
			$columns = array();
			$values = array();

			foreach (get_object_vars($this) as $k => $v) {
				if (in_array($k, $disallowedKeys)) {
					continue;
				}

				if (in_array($k, $allowedKeys) && isset($v)) {
					$columns[] = $k;
					$values[] = $v;
				}
			}

			$sql = 'INSERT INTO ' . $this->db->quoteName($this->tablename) . ' (' . implode(',', $this->db->quoteName($columns)) . ') VALUES (' . implode(',', $this->db->quote($values)) . ')';

			$result = $this->db->query($sql);

			if (!$result) {
				$this->error = $this->db->error;
				return false;
			}

			if ($this->primarykey === 'id') {
				$this->id = $this->db->insert_id;
			}

			$this->isNew = false;

			return true;
		} else {
			$sets = array();

			foreach(get_object_vars($this) as $k => $v) {
				if (in_array($k, $disallowedKeys) || !isset($this->$k)) {
					continue;
				}

				if (in_array($k, $allowedKeys)) {
					$sets[] = $this->db->quoteName($k) . ' = ' . $this->db->quote($v);
				}
			}

			$sql = 'UPDATE ' . $this->db->quoteName($this->tablename) . ' SET ' . implode(',', $sets) . ' WHERE ' . $this->db->quoteName($this->primarykey) . ' = ' . $this->db->quote($this->{$this->primarykey});

			$result = $this->db->query($sql);

			if (!$result) {
				$this->error = $this->db->error;
				return false;
			}

			return true;
		}
	}

	// -> bool
	public function delete()
	{
		if (empty($this->{$this->primarykey})) {
			$this->error = 'Library error: No primary key value.';
			return false;
		}

		$sql = 'DELETE FROM ' . $this->db->quoteName($this->tablename) . ' WHERE ' . $this->db->quoteName($this->primarykey) . ' = ' . $this->db->quote($this->{$this->primarykey});

		$result = $this->db->query($sql);

		if (!$result) {
			$this->error = $this->db->error;
			return false;
		}

		if ($this->primarykey === 'id') {
			$this->id = null;
		}

		$this->isNew = true;

		return true;
	}

	// -> bool
	public function refresh()
	{
		if (empty($this->{$this->primarykey})) {
			$this->error = 'Library error: No primary key value.';
			return false;
		}

		return $this->load($this->{$this->primarykey});
	}

	// array -> object
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

	public function link($table)
	{
		$classname = get_class($table);

		$primarykey = $table->primarykey;

		if (!isset($table->$primarykey)) {
			$this->error = 'Table error. No primary key value found in the provided table to link.';
			return false;
		}

		$keyname = strtolower(str_replace('Table', '', $classname)) . '_' . $primarykey;

		$this->$keyname = $table->$primarykey;

		return true;
	}
}
