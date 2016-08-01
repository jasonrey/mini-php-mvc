<?php namespace Mini\Lib;
!defined('MINI_EXEC') && die('No access.');

use \mysqli;
use \PDO;

class Database
{
	protected static $instances = array();

	protected $connection;

	protected static $adapters = array();

	protected $adapter;

	protected $statement;

	public $error;

	// (string = 'default') => $Database
	public static function getInstance($key = 'default')
	{
		if (empty($key)) {
			$key = 'default';
		}

		if (!isset(self::$instances[$key])) {
			$adapterClass = '';

			$dbconfig = \Mini\Config::getDBConfig($key);

			if (!empty($dbconfig['engine'])) {
				$adapterClass = ucfirst($dbconfig['engine']);
			} else {
				self::loadAdapter('legacy');
				$adapterClass = 'Legacy';
			}

			$adapterClass = '\\Mini\\Lib\\DatabaseAdapter\\' . $adapterClass;

			$instance = new $adapterClass($dbconfig);

			self::$instances[$key] = $instance;
		}

		return self::$instances[$key];
	}

	// (string) => bool
	public static function loadAdapter($engine)
	{
		if (!isset(self::$adapters[$engine])) {
			$file = dirname(__FILE__) . '/database-adapters/' . $engine . '.php';

			self::$adapters[$engine] = file_exists($file);

			if (self::$adapters[$engine]) {
				require_once($file);
			}
		}

		return self::$adapters[$engine];
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
			} catch (\PDOException $error) {
				$connection = false;
				$this->error = $error->getMessage();
			}
		}

		$this->connection = $connection;
	}

	// (string, array = array()) => bool
	public function query($string, $values = array())
	{
		$this->statement = $this->connection->prepare($string);

		return $this->statement->execute($values);
	}

	// () => int
	public function getInsertId()
	{
		return $this->connection->lastInsertId();
	}

	public function disconnect()
	{
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
			'columnCount',
			'closeCursor',
			'debugDumpParams',
			'errorCode',
			'errorInfo',
			'setFetchMode'
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
