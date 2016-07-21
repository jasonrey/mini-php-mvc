<?php

!defined('SERVER_EXEC') && die('No access.');

class Database
{
	protected static $instances = array();

	protected $connection;

	protected static $adapters = array();

	protected $adapter;

	protected $statement;

	public static function getInstance($key = 'default')
	{
		if (empty($key)) {
			$key = 'default';
		}

		if (!isset(self::$instances[$key])) {
			$adapterClass = 'Database';

			$dbconfig = Config::getDBConfig($key);

			if (!empty($dbconfig['engine']) && Database::loadAdapter($dbconfig['engine'])) {
				$adapterClass = ucfirst($dbconfig['engine']) . $adapterClass;
			}

			$instance = new $adapterClass($dbconfig);

			self::$instances[$key] = $instance;
		}

		return self::$instances[$key];
	}

	public static function loadAdapter($engine)
	{
		if (!isset(Database::$adapters[$engine])) {
			$file = dirname(__FILE__) . '/databases/' . $engine . '.php';

			Database::$adapters[$engine] = file_exists($file);

			if (Database::$adapters[$engine]) {
				require_once($file);
			}
		}

		return Database::$adapters[$engine];
	}

	public function __construct($dbconfig = array())
	{
		// v1.0 support
		if (!empty($dbconfig['server'])) {
			$connection = new mysqli($dbconfig['server'], $dbconfig['username'], base64_decode($dbconfig['password']), $dbconfig['database']);

			if ($connection->connect_error) {
				$connection = false;
				$this->error = $connection->connect_error;
			}
		} else {
			// v2.0 PDO
			try {
				switch ($dbconfig['engine']) {
					case 'mssql':
						$connection = new PDO('mssql:host=' . $dbconfig['host'] .';dbname=' . $dbconfig['db'] . ', ' . $dbconfig['un'] . ', ' . $dbconfig['pw']);
					break;

					case 'mysql':
					default:
						$connection = new PDO('mysql:host=' . $dbconfig['host'] .';dbname=' . $dbconfig['db'], $dbconfig['un'], $dbconfig['pw']);
					break;
				}
			} catch (PDOException $error) {
				$connection = false;
				$this->error = $error->getMessage();
			}
		}

		$this->connection = $connection;
	}

	public function query($string, $values = array())
	{
		$this->statement = $this->connection->prepare($string);
		$this->statement = $this->statement->execute($values);

		return $this;
	}

	// v2.0 - Deprecated
	public function quote($value)
	{
		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$value[$k] = $this->quote($v);
			}

			return $value;
		} else {
			return '\'' . $this->escape($value) . '\'';
		}
	}

	// v2.0 - Deprecated
	public function q($value)
	{
		return $this->quote($value);
	}

	// v2.0 - Deprecated
	public function quoteName($name, $as = null)
	{
		if (is_string($name)) {
			$quotedName = $this->quoteNameArrayString(explode('.', $name));

			$quotedAs = '';

			if (!is_null($as)) {
				settype($as, 'array');
				$quotedAs .= ' AS ' . $this->quoteNameArrayString($as);
			}

			return $quotedName . $quotedAs;
		} else {
			$fin = array();

			if (is_null($as)) {
				foreach ($name as $str) {
					$fin[] = $this->quoteName($str);
				}

				return $fin;
			}

			if (is_array($name) && (count($name) == count($as))) {
				$count = count($name);

				for ($i = 0; $i < $count; $i++) {
					$fin[] = $this->quoteName($name[$i], $as[$i]);
				}

				return $fin;
			}
		}
	}

	// v2.0 - Deprecated
	public function qn($name, $as = null)
	{
		return $this->quoteName($name, $as);
	}

	// v2.0 - Deprecated
	protected function quoteNameArrayString($array)
	{
		$parts = array();

		foreach ($array as $part) {
			$parts[] = '`' . $this->escape($part) . '`';
		}

		return implode('.', $parts);
	}

	// v2.0 - Deprecated
	public function escape($text) {
		return $this->connection->real_escape_string($text);
	}

	public function disconnect() {
		$this->connection = null;

		return true;
	}

	public function __call($name, $arguments)
	{
		if (in_array($name, array(
			'fetch',
			'fetchAll',
			'fetchColumn',
			'fetchObject',
			'execute',
			'rowCount',
			'columnCount'
		))) {
			return call_user_func_array(array($this->statement, $name), $arguments);
		}

		return call_user_func_array(array($this->connection, $name), $arguments);
	}

	public function __get($name)
	{
		return $this->connection->$name;
	}
}
