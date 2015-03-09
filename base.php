<?php

!defined('SERVER_EXEC') && die('No access.');

class Lib
{
    public static function ajax()
    {
        static $loaded;

        if (empty($loaded)) {
            require_once(__DIR__ . '/ajax.php');

            $loaded = true;
        }

        return Ajax::init();
    }

    public static function db()
    {
        static $loaded;

        if (empty($loaded)) {
            require_once(__DIR__ . '/db.php');

            $loaded = true;
        }

        return DB::init();
    }

    public static function view($name)
    {
        static $loaded;
        static $viewsLoaded = array();

        if (empty($loaded)) {
            require_once(__DIR__ . '/view.php');

            $loaded = true;
        }

        if (!in_array($name, $viewsLoaded)) {
            require_once(__DIR__ . '/../views/' . $name . '.php');

            $viewsLoaded[] = $name;
        }

        $classname = ucfirst($name) . 'View';

        $view = new $classname;

        $view->viewname = $name;

        return $view;
    }

    public static function output($namespace, $vars = array())
    {
        $segments = explode('/', $namespace);
        $view = array_shift($segments);
        $path = implode('/', $segments);

        $class = Lib::view($view);

        $class->set($vars);

        return $class->output($path);
    }

    public static function model($name)
    {
        static $loaded;
        static $modelsLoaded = array();

        if (empty($loaded)) {
            require_once(__DIR__ . '/model.php');

            $loaded = true;
        }

        if (empty($modelsLoaded[$name])) {
            require_once(__DIR__ . '/../models/' . $name . '.php');

            $classname = ucfirst($name) . 'Model';

            $model = new $classname;

            $model->tablename = $name;

            $modelsLoaded[$name] = $model;
        }

        return $modelsLoaded[$name];
    }

    public static function table($name)
    {
        static $loaded;
        static $tablesLoaded = array();

        if (empty($loaded)) {
            require_once(__DIR__ . '/table.php');

            $loaded = true;
        }

        if (!in_array($name, $tablesLoaded)) {
            require_once(__DIR__ . '/../tables/' . $name . '.php');

            $tablesLoaded[] = $name;
        }

        $classname = ucfirst($name) . 'Table';

        $table = new $classname;

        $table->tablename = $name;

        return $table;
    }
}