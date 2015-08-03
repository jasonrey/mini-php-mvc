<?php

!defined('SERVER_EXEC') && die('No access.');

class Database
{
	private static $instances = null;

	private $connection = null;

	public static function getInstance($key = 'default')
	{
		if (empty($key)) {
			$key = 'default';
		}

		if (!isset(self::$instances[$key])) {
			$instance = new self($key);

			self::$instances[$key] = $instance;
		}

		return self::$instances[$key];
	}

	public function __construct($key = null)
	{
		$dbconfig = Config::getDBConfig($key);

		$connection = new mysqli($dbconfig['server'], $dbconfig['username'], base64_decode($dbconfig['password']), $dbconfig['database']);

		if ($connection->connect_error) {
			throw new Exception('Connection failed: ' . $connection->connect_error);
		}

		$connection->set_charset('utf8');

		$this->connection = $connection;
	}

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

	public function q($value)
	{
		return $this->quote($value);
	}

	public function quoteName($name, $as = null)
	{
		if (is_string($name)) {
			$quotedName = $this->quoteNameStr(explode('.', $name));

			$quotedAs = '';

			if (!is_null($as)) {
				settype($as, 'array');
				$quotedAs .= ' AS ' . $this->quoteNameStr($as);
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

	public function qn($name, $as = null)
	{
		return $this->quoteName($name, $as);
	}

	protected function quoteNameStr($array)
	{
		$parts = array();

		foreach ($array as $part) {
			$parts[] = '`' . $part . '`';
		}

		return implode('.', $parts);
	}

	public function escape($text) {
		return $this->connection->real_escape_string($text);
	}

	public function disconnect() {
		return $this->connection->close();
	}

	public function __call($name, $arguments)
	{
		return call_user_func_array(array($this->connection, $name), $arguments);
	}

	public function __get($name)
	{
		return $this->connection->$name;
	}
}
