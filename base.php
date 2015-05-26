<?php

!defined('SERVER_EXEC') && die('No access.');

require_once('constant.php');

class Lib
{
    public static function ajax()
    {
        static $loaded;

        if (empty($loaded)) {
            require_once(dirname(__FILE__) . '/ajax.php');

            $loaded = true;
        }

        return Ajax::init();
    }

    public static function db()
    {
        static $loaded;

        if (empty($loaded)) {
            require_once(dirname(__FILE__) . '/db.php');

            $loaded = true;
        }

        return DB::init();
    }

    public static function view($name)
    {
        static $loaded;
        static $viewsLoaded = array();

        if (empty($loaded)) {
            require_once(dirname(__FILE__) . '/view.php');

            $loaded = true;
        }

        if (!in_array($name, $viewsLoaded)) {
            require_once(dirname(__FILE__) . '/../views/' . $name . '.php');

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
            require_once(dirname(__FILE__) . '/model.php');

            $loaded = true;
        }

        if (empty($name)) {
            $model = new Model;

            return $model;
        }

        if (empty($modelsLoaded[$name])) {
            require_once(dirname(__FILE__) . '/../models/' . $name . '.php');

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
            require_once(dirname(__FILE__) . '/table.php');

            $loaded = true;
        }

        if (!in_array($name, $tablesLoaded)) {
            require_once(dirname(__FILE__) . '/../tables/' . $name . '.php');

            $tablesLoaded[] = $name;
        }

        $classname = ucfirst($name) . 'Table';

        $table = new $classname;

        $table->tablename = $name;

        return $table;
    }

    public static function url($target, $options = array())
    {
        $values = array();

        foreach ($options as $k => $v) {
            $values[] = $k . '=' . $v;
        }

        $queries = implode('&', $values);

        if (!empty($queries)) {
            $queries = '?' . $queries;
        }

        return $target . '.php' . $queries;
    }

    public static function session()
    {
        static $loaded;

        if (empty($loaded)) {
            require_once(dirname(__FILE__) . '/session.php');

            $loaded = true;
        }

        return Session::init();
    }
}

// Initiate session first
Lib::session();