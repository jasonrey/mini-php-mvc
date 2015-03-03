<?php

!defined('SERVER_EXEC') && die('No access.');

class DB
{
    private static $instance = null;

    private $connection = null;

    public static function init()
    {
        if (empty(self::$instance)) {
            require_once(__DIR__ . '/dbconfig.php');

            $connection = new mysqli($servername, $username, $password, $dbname);

            if ($connection->connect_error) {
                throw new Exception('Connection failed: ' . $connection->connect_error);
            }

            $instance = new self;

            $instance->connection = $connection;

            self::$instance = $instance;
        }

        return self::$instance;
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