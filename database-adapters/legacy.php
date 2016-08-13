<?php namespace Mini\Lib\DatabaseAdapter;
!defined('MINI_EXEC') && die('No access.');

// v2.0 - Deprecated
// For v1.0 purposes
class Legacy extends \Mini\Lib\Database
{
	// Forward compatibility with v2.0 for library purposes
	public function query($string, $values = array())
	{
		$counter = 0;

		$self = $this;

		$remaining = array();

		$result = preg_replace_callback('/\?{3}|\?{2}|\?/', function ($matches) use (&$counter, &$remaining, $values, $self) {
			if (!isset($values[$counter])) {
				$replace = $matches[0];
			} else {
				switch ($matches[0]) {
					case '?':
						$replace = $self->quote($values[$counter]);
					break;
					case '??':
						$replace = $self->quoteName($values[$counter]);
					break;
					case '???':
						$replace = $self->escape($values[$counter]);
					break;
				}
			}

			$counter++;

			return $replace;
		}, $string);

		$this->result = $this->connection->query($result);

		return $this->result;
	}

	// Forward compatibility with v2.0 for library purposes
	public function fetch($class = null)
	// public function fetch($mode = PDO::FETCH_OBJ)
	{
		$row = $this->result->fetch_object();

		if (empty($class)) {
			return $row;
		}

		$object = new $class;

		foreach ($row as $key => $value) {
			$object->$key = $value;
		}

		return $object;
	}

	// Forward compatibility with v2.0 for library purposes
	public function fetchAll($class = null)
	{
		if (empty($class)) {
			$result = array();

			while ($row = $this->result->fetch_object()) {
				$result[] = $row;
			}

			return $result;
		}

		$result = $this->result->fetch_all(MYSQLI_ASSOC);

		$rows = array();

		foreach ($result as $row) {
			$record = new $class;

			foreach ($row as $key => $value) {
				$record->$key = $value;
			}

			$rows[] = $record;
		}

		return $rows;
	}

	// Forward compatibility with v2.0 for library purposes
	public function errorInfo()
	{
		return array(
			'',
			$this->connection->errno,
			$this->connection->error
		);
	}

	// Forward compatibility with v2.0 for library purposes
	public function rowCount()
	{
		return $this->connection->affected_rows;
	}

	// () => int
	public function getInsertId()
	{
		return $this->connection->insert_id;
	}

	public function disconnect()
	{
		return $this->connection->close();
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
	public function escape($text)
	{
		return $this->connection->real_escape_string($text);
	}
}
