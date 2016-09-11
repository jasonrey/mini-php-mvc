<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use \Mini\Lib;

abstract class Table
{
	// v2.0 - Changed to static
	// v2.0 - Mandatory parameter
	public static $tablename;

	// v2.0 - Supports multiple primary key with array
	public static $primarykey = 'id';

	public static $activedb = 'default';

	// v2.0 - Columns
	public static $columns = array(
		'id' => 'int',
	);

	// v2.0 - Foreign keys
	public static $foreigns = array();

	public $isNew = true;
	public $error;

	public function __construct($data = array())
	{
		foreach (array_keys(static::$columns) as $key) {
			$this->$key = null;
		}

		$this->bind($data);
	}

	public static function tableExist()
	{
		$db = self::getDB();

		try {
			$result = $db->getColumns(static::getTableName());
		} catch (\Exception $error) {
			return false;
		}

		return true;
	}

	public static function createTable()
	{
		$file = Lib::path('schemas/' . static::getTableName() . '.sql');

		if (!file_exists($file)) {
			throw new \Exception('Schema file for ' . static::getTableName() . ' doesn\'t exist.');
		}

		$db = self::getDB();

		if (!$db->query(file_get_contents($file))) {
			throw new \Exception($db->errorInfo()[2]);
		}

		return true;
	}

	public static function getTableName()
	{
		if (empty(static::$tablename)) {
			static::$tablename = strtolower(str_replace('Mini\\Table\\', '', get_called_class()));
		}

		return static::$tablename;
	}

	// Get current table primary keys as array
	// () => array
	public static function getPrimaryKeys()
	{
		if (empty(static::$primarykey)) {
			return array();
		}

		return is_array(static::$primarykey) ? static::$primarykey : array(static::$primarykey);
	}

	// Get the database connect
	// () => $Database
	public static function getDB()
	{
		return Database::get(static::$activedb);
	}

	// Load a record into current class
	// (array|int|string, int|string...) => bool
	public function load($keys)
	{
		$arguments = func_get_args();
		$totalArgs = func_num_args();

		$db = self::getDB();

		if ($db->error) {
			$this->error = $db->error;
			return false;
		}

		$primarykeys = self::getPrimaryKeys();

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

		$queryValues = array(static::getTableName());

		$wheres = array();

		foreach ($keys as $k => $v) {
			$wheres[] = '?? = ?';
			$queryValues[] = $k;
			$queryValues[] = $v;
		}

		$sql .= implode(' AND ', $wheres) . ' LIMIT 1';

		$db->query($sql, $queryValues);

		$row = $db->fetchObject();

		if (empty($row)) {
			// If no record found, then prepopulate it with values first
			$this->bind($keys);

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

	// Save current record
	// (array|object) => bool
	public function bind($keys, $strict = false)
	{
		if (!is_array($keys) && !is_object($keys)) {
			$this->error = 'Library error: accepted argument is not iteratable.';
			return false;
		}

		if (is_bool($strict)) {
			foreach ($keys as $k => $v) {
				if ($strict && !isset(static::$columns[$k])) {
					continue;
				}

				$this->set($k, $v);
			}
		}

		if (is_array($strict) || is_object($strict)) {
			foreach ($keys as $k => $v) {
				if (isset(static::$columns[$k])) {
					$this->set($k, $v);
				} else {
					foreach ($strict as $column => $join) {
						if (!isset(static::$foreigns[$column])) {
							continue;
						}

						$tableclass = '\\Mini\\Table\\' . static::$foreigns[$column]['classname'];

						if (isset($tableclass::$columns[$k])) {
							$this->$k = $tableclass::normalize($k, $v);
						}
					}
				}
			}
		}

		return true;
	}

	// Alias to store
	// () => bool
	public function save()
	{
		return $this->store();
	}

	// Update/insert current record
	// () => bool
	public function store()
	{
		$primarykeys = self::getPrimaryKeys();

		$allowedKeys = array_keys(static::$columns);

		$db = self::getDB();

		if ($db->error) {
			$this->error = $db->error;
			return false;
		}

		// Autopopulate Date
		if (self::hasColumn('date') && !$this->has('date')) {
			$this->set('date', date('Y-m-d H:i:s'));
		}

		// Autopopulate Created
		if (self::hasColumn('created') && !$this->has('created')) {
			$this->set('created', date('Y-m-d H:i:s'));
		}

		// Autopopulate IP
		if (self::hasColumn('ip') && !$this->has('ip')) {
			$this->ip = $_SERVER['REMOTE_ADDR'];
		}

		if ($this->isNew) {
			$sql = 'INSERT INTO ?? ';
			$queryValues = array(static::getTableName());

			$columns = array();
			$values = array();

			$count = 0;

			foreach ($allowedKeys as $key) {
				if ($key === 'id') {
					continue;
				}

				if (isset($this->$key)) {
					$count++;

					$columns[] = $key;
					$values[] = $this->$key;
				}
			}

			if ($count > 0) {
				$sql .= '(' . implode(', ', array_fill(0, $count, '??')) . ') VALUES ';
				$sql .= '(' . implode(', ', array_fill(0, $count, '?')) . ')';
			}

			$queryValues = array_merge($queryValues, $columns, $values);

			if (!$db->query($sql, $queryValues)) {
				$this->error = $db->errorInfo()[2];
				return false;
			}

			$insertId = $db->getInsertId();

			if (!empty($insertId) && !empty($primarykeys)) {
				$this->{$primarykeys[0]} = $insertId;
			}

			$this->isNew = false;

			return true;
		} else {
			$sql = 'UPDATE ?? SET ';

			$queryValues = array(static::getTableName());

			$sets = array();

			foreach(get_object_vars($this) as $k => $v) {
				if (!isset($this->$k)) {
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

			if (!$db->query($sql, $queryValues)) {
				$this->error = $db->errorInfo()[2];
				return false;
			}

			return true;
		}
	}

	// Deletes current record
	// () => bool
	public function delete()
	{
		$primarykeys = self::getPrimaryKeys();

		$db = self::getDB();

		if ($db->error) {
			$this->error = $db->error;
			return false;
		}

		if (empty($primarykeys)) {
			$this->error = 'Library error: No primary key value.';
			return false;
		}

		$sql = 'DELETE FROM ?? WHERE ';

		$queryValues = array(static::getTableName());

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

		if (!$db->query($sql, $queryValues)) {
			$this->error = $db->errorInfo()[2];
			return false;
		}

		if ($db->rowCount() === 0) {
			$this->error = 'No record deleted';
			return false;
		}

		if (static::$primarykey === 'id') {
			$this->id = null;
		}

		$this->isNew = true;

		return true;
	}

	// Refresh current record
	// () => bool
	public function refresh()
	{
		$primarykeys = self::getPrimaryKeys();

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

	// Export the table to a stdClass with allowed keys
	// (array) => object
	public function export($keys = array())
	{
		$allowedKeys = array_keys(static::$columns);

		if (!empty($keys)) {
			$allowedKeys = array_intersect($allowedKeys, $keys);
		}

		$obj = new stdClass();

		foreach (get_object_vars($this) as $k => $v) {
			if (!in_array($k, $allowedKeys)) {
				continue;
			}

			$obj->$k = self::normalize($k, $v);
		}

		return $obj;
	}

	// Links a foreign key
	// ($Table) => bool
	public function link($table)
	{
		$classname = get_class($table);

		$primarykeys = $classname::getPrimaryKeys();

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

	// v2.0
	// Get a single record
	// Alias to Table::get(), defined in __callStatic
	// (array|int|string, int|string...) => $Table
	public static function getRecord()
	{
		$table = new static();

		call_user_func_array(array($table, 'load'), func_get_args());

		return $table;
	}

	// v2.0
	// Create a single record
	// (array|object) => $Table
	public static function create($data = array())
	{
		$table = new static();

		if (empty($data)) {
			foreach (array_keys(static::$columns) as $key) {
				$data[$key] = '';
			}
		}

		$table->bind($data);

		$table->store();

		return $table;
	}

	// v2.0
	// Delete a single record
	// (array|int|string, int|string...) => bool
	public static function destroy($keys)
	{
		$table = self::getRecord($keys);

		if (empty($table->error)) {
			$table->delete();
		}

		return $table;
	}

	// v2.0
	// Get table records
	// (array = array(), array = array(), array = array()) => array
	// If table have static::$foreigns defined: $conditions, $through, $ordering, $limit
	// Else: $conditions, $ordering, $limit
	public static function all()
	{
		$args = func_get_args();

		$conditions = array_shift($args);

		$through = array();

		if (!empty(static::$foreigns)) {
			$through = array_shift($args);
		}

		$ordering = array_shift($args);

		$limit = array_shift($args);

		$sql = 'SELECT * FROM ??';

		$queryValues = array(static::getTableName());

		$joins = array();
		$joinsColumns = array();
		$joinsValues = array();

		if (!empty($through)) {
			foreach ($through as $column => $join) {
				if (!isset(static::$foreigns[$column])) {
					continue;
				}

				$tableclass = '\\Mini\\Table\\' . static::$foreigns[$column]['classname'];

				$joinstring = '';

				if (empty($join['type'])) {
					$join['type'] = 'left';
				}

				$joinstring .= $join['type'] . ' join ?? as ??';

				$joinsValues[] = $tableclass::$tablename;

				if (empty($join['alias'])) {
					$join['alias'] = $tableclass::$tablename;
				}

				$joinsValues[] = $join['alias'];

				$joinstring .= ' on ??.?? = ??.??';

				$joinsValues[] = static::getTableName();
				$joinsValues[] = $column;
				$joinsValues[] = $join['alias'];
				$joinsValues[] = static::$foreigns[$column]['column'];

				$joins[] = $joinstring;

				if (!empty($join['columns'])) {
					foreach ($join['columns'] as $joinColumn) {
						$joinsColumns[] = $join['alias'] . '.' . $joinColumn;
					}
				}
			}
		}

		if (!empty($joins)) {
			$columns = array_fill(0, count($joinsColumns), '??');

			array_unshift($columns, '??.*');

			$sql = 'SELECT ' . implode(', ', $columns) . ' FROM ?? AS ??';

			$queryValues = $joinsColumns;

			array_unshift($queryValues, static::getTableName());

			$queryValues[] = static::getTableName();
			$queryValues[] = static::getTableName();

			$sql .= ' ' . implode(' ', $joins);
			$queryValues = array_merge($queryValues, $joinsValues);
		}

		$wheres = array();

		if (!empty($conditions)) {
			foreach ($conditions as $key => $value) {
				$queryValues[] = $key;

				if (is_array($value)) {
					$wheres[] = '?? IN (' . implode(',', array_fill(0, count($value), '?')) . ')';
					$queryValues = array_merge($queryValues, $value);
				} else {
					$wheres[] = '?? = ?';
					$queryValues[] = $value;
				}
			}

			$sql .= ' WHERE ' . implode(' AND ', $wheres);
		}

		if (!empty($ordering)) {
			$sql .= ' ORDER BY ' . implode(',', $ordering);
		}

		if (!empty($limit)) {
			$sql .= ' LIMIT ' . $limit;
		}

		$db = self::getDB();

		if ($db->error) {
			return array();
		}

		if (!$db->query($sql, $queryValues)) {
			return array();
		}

		$result = $db->fetchAll();

		if (empty($result)) {
			return array();
		}

		$rows = array();

		foreach ($result as $row) {
			$table = new static();

			$table->bind($row);
			$table->isNew = false;

			$rows[] = $table;
		}

		return $rows;
	}

	public static function getResult($sql, $queryValues = array(), $bindTable = true)
	{
		$db = self::getDB();

		if ($db->error) {
			return array();
		}

		if (!$db->query($sql, $queryValues)) {
			return array();
		}

		$result = $db->fetchAll();

		if (empty($result)) {
			return array();
		}

		if (!$bindTable) {
			return $result;
		}

		$rows = array();

		foreach ($result as $row) {
			$table = new static();

			$table->bind($row);
			$table->isNew = false;

			$rows[] = $table;
		}

		return $rows;
	}

	public static function getColumn($sql, $queryValues = array())
	{
		$db = self::getDB();

		if ($db->error) {
			return array();
		}

		if (!$db->query($sql, $queryValues)) {
			return array();
		}

		$result = $db->fetchColumn();

		if (empty($result)) {
			return array();
		}

		return $result;
	}

	public static function getRow($sql, $queryValues = array(), $bindTable = true)
	{
		$db = self::getDB();

		if ($db->error) {
			return array();
		}

		if (!$db->query($sql, $queryValues)) {
			return array();
		}

		$result = $db->fetch();

		$table = new static();

		$table->bind($result);
		$table->isNew = false;

		return $table;
	}

	public static function getCell($sql, $queryValues = array())
	{
		$db = self::getDB();

		if ($db->error) {
			return array();
		}

		if (!$db->query($sql, $queryValues)) {
			return array();
		}

		$result = $db->fetchColumn();

		if (empty($result)) {
			return null;
		}

		return $result[0];
	}

	// v2.0
	// Get table count
	// (array = array()) => int
	public static function count($conditions = array())
	{
		$sql = 'SELECT COUNT(1) AS `total` FROM ??';

		$wheres = array();
		$queryValues = array(static::getTableName());

		if (!empty($conditions)) {
			foreach ($conditions as $key => $value) {
				$wheres[] = '?? = ?';
				$queryValues[] = $key;
				$queryValues[] = $value;
			}

			$sql .= ' WHERE ' . implode(' AND ', $wheres);
		}

		$db = self::getDB();

		if ($db->error) {
			return 0;
		}

		if (!$db->query($sql, $queryValues)) {
			return 0;
		}

		$result = $db->fetch();

		if (empty($result)) {
			return 0;
		}

		return (int) $result->total;
	}

	// v2.0
	// Preferred set property method in order to normalize value
	// (string, string) => null
	public function set($key, $value)
	{
		$this->$key = self::normalize($key, $value);

		return $this;
	}

	// v2.0
	// Check if column has value
	// (string) => bool
	public function has($column)
	{
		return isset($this->$column);
	}

	// v2.0
	// Check if column has value
	// (string) => bool
	public static function hasColumn($column)
	{
		return isset(static::$columns[$column]);
	}

	// v2.0
	// Normalize values based on defined type
	// (string, string) => string|int|float
	public static function normalize($column, $value)
	{
		if (!isset(static::$columns[$column])) {
			return $value;
		}

		switch (static::$columns[$column]) {
			case 'int':
			case 'tinyint':
			case 'mediumint':
			case 'bigint':
				return (int) $value;
			case 'float':
			case 'double':
				return (float) $value;
			case 'string':
			case 'char':
			case 'varchar':
			case 'text':
			case 'tinytext':
			case 'mediumtext':
			case 'longtext':
				if (is_array($value) || is_object($value)) {
					return json_encode($value);
				}

				return (string) $value;
			case 'date':
				if (is_object($value) && get_class($value) === 'DateTime') {
					return $value->format('Y-m-d');
				}
			case 'timestamp':
			case 'datetime':
				if (is_object($value) && get_class($value) === 'DateTime') {
					return $value->format('Y-m-d H:i:s');
				}
			case 'time':
				if (is_object($value) && get_class($value) === 'DateTime') {
					return $value->format('H:i:s');
				}
		}

		return $value;
	}

	public static function reorder(&$result, $column, $direction = 'asc')
	{
		usort($result, function($a, $b) use ($column, $direction) {
			if ($a->$column == $b->$column) {
				return 0;
			}

			if ($a->$column < $b->$column) {
				return $direction === 'asc' ? -1 : 1;
			}

			if ($a->$column > $b->$column) {
				return $direction === 'asc' ? 1 : -1;
			}
		});

		return $result;
	}

	public static function regroup(&$result, $column)
	{
		$rows = array();

		foreach ($result as $row) {
			$rows[$row->$column] = $row;
		}

		$result = $rows;

		return $result;
	}

	// (array(), [string], string)
	public static function buildTree(&$result, $columns, $groupkey = null)
	{
		$rows = array();

		$ref = array();

		foreach ($result as $row) {
			$prevRef = &$ref;

			$row = (object) $row;

			foreach ($columns as $c) {
				if (!isset($prevRef[$row->$c])) {
					$prevRef[$row->$c] = array();
				}

				$prevRef = &$prevRef[$row->$c];
			}

			if (empty($groupkey) || !isset($row->$groupkey)) {
				$prevRef[] = $row;
			} else {
				$prevRef[$row->$groupkey] = $row;
			}
		}

		$result = $ref;

		return $ref;
	}

	public static function __callStatic($name, $arguments)
	{
		switch ($name) {
			case 'get':
				return call_user_func_array('static::getRecord', $arguments);
			break;
		}
	}

	public function __call($name, $arguments)
	{
		$totalArgs = count($arguments);

		// (string) => string
		// () => object
		if ($name === 'get') {
			if ($totalArgs === 0) {
				return $this->export();
			}

			return $this->{$arguments[0]};
		}
	}
}
