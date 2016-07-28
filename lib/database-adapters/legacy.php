<?php namespace Mini\Lib\DatabaseAdapter;
!defined('SERVER_EXEC') && die('No access.');

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
	public function fetch($mode = PDO::FETCH_OBJ)
	{
		switch ($mode) {
			case PDO::FETCH_NAMED:
			case PDO::FETCH_ASSOC:
				return $this->result->fetch_array(MYSQLI_ASSOC);
			case PDO::FETCH_BOTH:
				return $this->result->fetch_array();
			case PDO::FETCH_NUM:
				return $this->result->fetch_array(MYSQLI_NUM);
			case PDO::FETCH_CLASS:
				$row = $this->result->fetch_object();

				if (func_num_args() > 1) {
					$classname = func_get_args()[1];

					$object = new $classname;

					foreach ($row as $key => $value) {
						$object->$key = $value;
					}

					return $object;
				}

				return $row;
			case PDO::FETCH_INTO:
				$row = $this->result->fetch_object();

				if (func_num_args() > 1) {
					$object = func_get_args()[1];

					if (!is_object($object)) {
						return $row;
					}

					foreach ($row as $key => $value) {
						$object->$key = $value;
					}

					return $object;
				}

				return $row;

			case PDO::FETCH_OBJ:
			default:
				return $this->result->fetch_object();
		}
	}

	// Forward compatibility with v2.0 for library purposes
	public function fetchAll($mode)
	{
		switch ($mode) {
			case PDO::FETCH_CLASS:
			case PDO::FETCH_CLASS | PDO::FETCH_PROPS_LATE:
				$result = $this->result->fetch_all(MYSQLI_ASSOC);

				$classname = func_get_args()[1];
				Lib::load('table', strtolower(str_replace('Table', '', $classname)));

				$rows = array();

				foreach ($result as $row) {
					$record = new $classname;

					foreach ($row as $key => $value) {
						$record->$key = $value;
					}

					$rows[] = $record;
				}

				return $rows;

			case PDO::FETCH_BOTH:
				$result = array();

				while ($row = $this->result->fetch_array()) {
					$result[] = $row;
				}

				return $result;

			case PDO::FETCH_ASSOC:
				$result = array();

				while ($row = $this->result->fetch_array(MYSQLI_ASSOC)) {
					$result[] = $row;
				}

				return $result;

			case PDO::FETCH_NUM:
				$result = array();

				while ($row = $this->result->fetch_array(MYSQLI_NUM)) {
					$result[] = $row;
				}

				return $result;

			case PDO::FETCH_OBJ:
			default:
				$result = array();

				while ($row = $this->result->fetch_object()) {
					$result[] = $row;
				}

				return $result;
		}

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
