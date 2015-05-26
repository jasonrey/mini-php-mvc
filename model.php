<?php

!defined('SERVER_EXEC') && die('No access.');

class Model
{
    public $tablename;

    public static $db;

    public $active = 'db';

    public function __construct()
    {
        if (empty(self::$db)) {
            self::$db = Lib::db();
        }
    }

    public function getResult($sql, $bindTable = true)
    {
        $result = self::${$this->active}->query($sql);

        if ($result === false) {
            throw new Exception(self::${$this->active}->error);
        }

        if ($result->num_rows === 0) {
            return array();
        }

        $tables = array();

        if (!empty($this->tablename) && $bindTable) {
            while ($row = $result->fetch_object()) {
                $table = Lib::table($this->tablename);
                $table->bind($row);

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
        $result = self::${$this->active}->query($sql);

        if ($result === false) {
            throw new Exception(self::${$this->active}->error);
        }

        if ($result->num_rows === 0) {
            return array();
        }

        $tables = array();

        if (!empty($this->tablename) && $bindTable) {
            while ($row = $result->fetch_object()) {
                $table = Lib::table($this->tablename);
                $table->bind($row);

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
        $result = self::${$this->active}->query($sql);

        if ($result === false) {
            throw new Exception(self::${$this->active}->error);
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
        $result = self::${$this->active}->query($sql);

        if ($result === false) {
            throw new Exception(self::${$this->active}->error);
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
                $orders[] = self::${$this->active}->quoteName($o) . ' ' . (isset($options['direction'][$i]) ? $options['direction'][$i] : $defaultDirection);
            }

            $string .= implode(',', $orders);

            return $string;
        }

        return ' ORDER BY ' . self::${$this->active}->quoteName(isset($options['order']) ? $options['order'] : $defaultOrder) . ' ' . (isset($options['direction']) ? $options['direction'] : $defaultDirection);
    }

    public function buildLimit($options = array(), $defaultStart = 0, $defaultLimit = 0)
    {
        if (empty($options['limit']) && empty($defaultLimit)) {
            return '';
        }

        return ' LIMIT ' . (isset($options['start']) ? $options['start'] : $defaultStart) . ',' . (!empty($options['limit']) ? $options['limit'] : $defaultLimit);
    }
}