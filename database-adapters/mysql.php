<?php namespace Mini\Lib\DatabaseAdapter;
!defined('MINI_EXEC') && die('No access.');

use \PDO;

class Mysql extends \Mini\Lib\Database
{
	// () => $PDO
	public function connect($dbconfig)
	{
		$connection = new PDO('mysql:host=' . $dbconfig['host'] . ';port=' . (!empty($dbconfig['port']) ? $dbconfig['port'] : '3306') . ';charset=utf8', $dbconfig['un'], $dbconfig['pw']);

		return $connection;
	}

	public function useDB($db)
	{
		$result = $this->query('use ??', array($db));

		if (!$result) {
			$error = $this->errorInfo();
			throw new \Exception($error[2]);
		}

		return $result;
	}

	public function getQuery($query, $values = array())
	{
		$counter = 0;

		$self = $this;

		$result = preg_replace_callback('/\?{3}|\?{2}|\?/', function ($matches) use (&$counter, $values, $self) {
			$replace = '?';

			if ($matches[0] !== '?') {
				if (!isset($values[$counter])) {
					$replace = $matches[0];
				} else {
					if ($matches[0] === '??') {
						$replace = $self->quoteName($values[$counter]);
					}

					if ($matches[0] === '???') {
						$replace = $values[$counter];
					}
				}
			}

			$counter++;

			return $replace;
		}, $query);

		return $result;
	}

	// (string, array) => bool
	public function query($query, $values = array())
	{
		$counter = 0;

		$self = $this;

		$remaining = array();

		$result = preg_replace_callback('/\?{3}|\?{2}|\?/', function ($matches) use (&$counter, &$remaining, $values, $self) {
			$replace = '?';

			if ($matches[0] === '?') {
				$remaining[] = $values[$counter];
			} else {
				if (!isset($values[$counter])) {
					$replace = $matches[0];
				} else {
					if ($matches[0] === '??') {
						$replace = $self->quoteName($values[$counter]);
					}

					if ($matches[0] === '???') {
						$replace = $values[$counter];
					}
				}
			}

			$counter++;

			return $replace;
		}, $query);

		$this->statement = $this->connection->prepare($result);

		return $this->statement->execute($remaining);
	}

	// (array|string) => string
	// v2.0 - Deprecated $as. This function will only be used privately for MySQL driver.
	public function quoteName($string, $as = null)
	{
		if (is_string($string) && strpos($string, '.')) {
			$string = explode('.', $string);

			foreach ($string as &$s) {
				$s = $this->quoteName($s);
			}

			$string = implode('.', $string);

			return $string;
		}

		if (is_array($string)) {
			$result = array();

			foreach ($string as $s) {
				$result[] = $this->quoteName($s);
			}

			return implode(', ', $result);
		}

		return '`' . str_replace('`', '``', $string) . '`';
	}

	// (string) => array()
	public function fetchAll($class = null)
	{
		if (empty($class)) {
			return $this->statement->fetchAll(\PDO::FETCH_OBJ);
		}

		return $this->statement->fetchAll(\PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE, $class);
	}

	public function fetchColumn()
	{
		return $this->statement->fetchAll(\PDO::FETCH_COLUMN);
	}

	// (string) => object|$Table
	public function fetch($class = null)
	{
		if (empty($class)) {
			$this->statement->setFetchMode(\PDO::FETCH_OBJ);
		} else {
			$this->statement->setFetchMode(\PDO::FETCH_CLASS, $class);
		}

		return $this->statement->fetch();
	}

	public function tableExist($table)
	{
		try {
			$this->getColumns($table);
		} catch (\Exception $error) {
			return false;
		}

		return true;
	}

	public function getColumns($table)
	{
		if (!$this->query('show columns from ??', array($table))) {
			throw new \Exception($db->errorInfo()[2]);
		}

		$result = $this->fetchAll();

		var_dump($result);

		return $result;
	}

	public static function checkColumns($table)
	{
		$columns = $this->getColumns($table);
	}
}
