<?php

!defined('SERVER_EXEC') && die('No access.');

class Model
{
	private static $instances = array();

	public $tablename;
	public $db;
	public $activedb = 'default';

	public static function getInstance($name = null)
	{
		if (empty($name)) {
			if (!isset(self::$instances['self'])) {
				self::$instances['self'] = new Model;
			}

			return self::$instances['self'];
		}

		if (!isset(self::$instances[$name])) {
			Lib::load('model', $name);

			$classname = ucfirst($name) . 'Model';

			self::$instances[$name] = new $classname;

			self::$instances[$name]->tablename = $name;
		}

		return self::$instances[$name];
	}

	public function __construct()
	{
		$this->db = Lib::db($this->activedb);
	}

	public function getResult($sql, $bindTable = true)
	{
		$result = $this->db->query($sql);

		if ($result === false) {
			throw new Exception($this->db->error);
		}

		if ($result->num_rows === 0) {
			return array();
		}

		$tables = array();

		if (!empty($this->tablename) && $bindTable) {
			while ($row = $result->fetch_object()) {
				$table = Lib::table($this->tablename);
				$table->bind($row);

				$table->isNew = false;

				$tables[] = $table;
			}
		} else {
			while ($row = $result->fetch_object()) {
				$tables[] = $row;
			}
		}

		return $tables;
	}

	public function getRow($sql, $bindTable = true)
	{
		$result = $this->db->query($sql);

		if ($result === false) {
			throw new Exception($this->db->error);
		}

		if ($result->num_rows === 0) {
			return array();
		}

		$tables = array();

		if (!empty($this->tablename) && $bindTable) {
			while ($row = $result->fetch_object()) {
				$table = Lib::table($this->tablename);
				$table->bind($row);

				$table->isNew = false;

				return $table;
			}
		} else {
			while ($row = $result->fetch_object()) {
				return $row;
			}
		}
	}

	public function getColumn($sql)
	{
		$result = $this->db->query($sql);

		if ($result === false) {
			throw new Exception($this->db->error);
		}

		if ($result->num_rows === 0) {
			return array();
		}

		$data = array();

		while ($row = $result->fetch_array()) {
			$data[] = $row[0];
		}

		return $data;
	}

	public function getCell($sql)
	{
		$result = $this->db->query($sql);

		if ($result === false) {
			throw new Exception($this->db->error);
		}

		if ($result->num_rows === 0) {
			return null;
		}

		$data = null;

		while ($row = $result->fetch_array()) {
			$data = $row[0];
			break;
		}

		return $data;
	}

	public function buildWhere($conditions = array())
	{
		if (empty($conditions)) {
			return '';
		}

		return ' WHERE ' . implode(' AND ', $conditions);
	}

	public function buildOrder($options = array(), $defaultOrder = 'id', $defaultDirection = 'asc')
	{
		if (isset($options['order']) && is_array($options['order'])) {
			$string = ' ORDER BY ';

			$orders = array();

			foreach ($options['order'] as $i => $o) {
				$orders[] = $this->db->quoteName($o) . ' ' . (isset($options['direction'][$i]) ? $options['direction'][$i] : $defaultDirection);
			}

			$string .= implode(',', $orders);

			return $string;
		}

		return ' ORDER BY ' . $this->db->quoteName(isset($options['order']) ? $options['order'] : $defaultOrder) . ' ' . (isset($options['direction']) ? $options['direction'] : $defaultDirection);
	}

	public function buildLimit($options = array(), $defaultStart = 0, $defaultLimit = 0)
	{
		if (empty($options['limit']) && empty($defaultLimit)) {
			return '';
		}

		return ' LIMIT ' . (isset($options['start']) ? $options['start'] : $defaultStart) . ',' . (!empty($options['limit']) ? $options['limit'] : $defaultLimit);
	}

	public function assignByKey($result, $key = 'id')
	{
		$rearranged = array();

		foreach ($result as $row) {
			$rearranged[$row->$key] = $row;
		}

		return $rearranged;
	}
}
