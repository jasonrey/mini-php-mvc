<?php

!defined('SERVER_EXEC') && die('No access.');

abstract class Table
{
    public $id;

    public $tablename;

    protected static $db;

    public function __construct()
    {
        if (empty(self::$db)) {
            self::$db = Lib::db();
        }
    }

    // array/int -> bool
    public function load($keys)
    {
        if (!is_array($keys)) {
            $keys = array('id' => $keys);
        }

        $sql = 'SELECT * FROM `' . $this->tablename . '` WHERE ';

        $wheres = array();

        foreach ($keys as $k => $v) {
            $wheres[] = '`' . $k . '` = \''  . $v . '\'';
        }

        $sql .= implode(' AND ', $wheres) . ' LIMIT 1';

        $result = self::$db->query($sql);

        if ($result->num_rows === 0) {
            return false;
        }

        $row = $result->fetch_object();

        return $this->bind($row);
    }

    // array/object -> bool
    public function bind($keys)
    {
        if (!is_array($keys) && !is_object($keys)) {
            return false;
        }

        foreach ($keys as $k => $v) {
            $this->$k = $v;
        }

        return true;
    }

    // -> bool
    public function store()
    {
        if (empty($this->id)) {
            $columns = array();
            $values = array();

            foreach (get_object_vars($this) as $k => $v) {
                if (in_array($k, array('tablename', 'id'))) {
                    continue;
                }

                $columns[] = $k;
                $values[] = $v;
            }

            $sql = 'INSERT INTO `' . $this->tablename . '` (`' . implode('`, `', $columns) . '`) VALUES (\'' . implode('\', \'', $values) . '\')';

            $result = self::$db->query($sql);

            if (!$result) {
                return false;
            }

            $this->id = self::$db->insert_id;

            return true;
        } else {
            $sets = array();

            foreach(get_object_vars($this) as $k => $v) {
                if (in_array($k, array('tablename', 'id')) || !isset($this->$k)) {
                    continue;
                }

                $sets[] = '`' . $k . '` = \'' . $v . '\'';
            }

            $sql = 'UPDATE `' . $this->tablename . '` SET ' . implode(',', $sets) . ' WHERE `id` = ' . $this->id;

            $result = self::$db->query($sql);

            if (!$result) {
                return false;
            }

            return true;
        }
    }

    // -> bool
    public function delete()
    {
        if (empty($this->id)) {
            return false;
        }

        $sql = 'DELETE FROM `' . $this->tablename . '` WHERE `id` = ' . $this->id;

        $result = self::$db->query($sql);

        if (!$result) {
            return false;
        }

        $this->id = null;

        return true;
    }
}