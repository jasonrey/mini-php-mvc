<?php

!defined('SERVER_EXEC') && die('No access.');

abstract class Model
{
    public $tablename;

    protected static $db;

    public function __construct()
    {
        if (empty(self::$db)) {
            self::$db = Lib::db();
        }
    }

    public function query($sql)
    {
        $result = self::$db->query($sql);

        if ($result->num_rows === 0) {
            return array();
        }

        $tables = array();

        while ($row = $result->fetch_object()) {
            $table = Lib::table($this->tablename);
            $table->bind($row);

            $tables[] = $table;
        }

        return $tables;
    }
}