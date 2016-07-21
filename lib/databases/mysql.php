<?php
!defined('SERVER_EXEC') && die('No access.');

class MysqlDatabase extends Database
{
	// MysqlDatabase (string, array)
	public function query($query, $values = array())
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

		$this->statement = $this->connection->prepare($result);

		$this->statement->execute($values);

		return $this;
	}

	// string (array)
	// string (string)
	// v2.0 - Deprecated $as. This function will only be used privately for MySQL driver.
	public function quoteName($string, $as = null)
	{
		if (is_array($string)) {
			$result = array();

			foreach ($string as $s) {
				$result[] = $this->quoteName($s);
			}

			return implode(', ', $result);
		}

		return '`' . str_replace('`', '``', $string) . '`';
	}
}
