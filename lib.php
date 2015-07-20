<?php
!defined('SERVER_EXEC') && die('No access.');

require_once('constant.php');
require_once('config.php');
require_once('req.php');

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
            require_once(dirname(__FILE__) . '/database.php');

            $loaded = true;
        }

        return Database::init();
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

    public static function route()
    {
        $request = rtrim(str_replace(Config::getBaseFolder(), '', $_SERVER['REQUEST_URI']), '/');

        if (empty($request)) {
            $request = 'index';
        }

        $segments = explode('/', $request);

        $key = strtolower(array_shift($segments));

        if ($key === 'index.php') {
            $key = 'index';
        }

        $router = Lib::router($key);

        if ($router === false) {
            // Require error
            return true;
        }

        $result = $router->route($segments);

        if ($result === false) {
            // Require error
            return true;
        }

        return true;
    }

    public static function router($name)
    {
        static $loaded;
        static $loadedRouters;

        if (!isset($loaded)) {
            require_once(dirname(__FILE__) . '/router.php');
            $loaded = true;
        }

        $key = trim(strtolower(preg_replace('/[' . preg_quote('-_', '/') . ']/', '', $name)));

        if (!isset($loadedRouters[$key])) {
            $file = dirname(__FILE__) . '/../routers/' . $key . '.php';

            if (!file_exists($file)) {
                return false;
            }

            require_once($file);

            $classname = ucfirst($key) . 'Router';

            $loadedRouters[$key] = new $classname;

            $loadedRouters[$key]->base = strtolower($name);
        }

        return $loadedRouters[$key];
    }

    public static function url($target, $options = array(), $external = false)
    {
        $values = array();

        $router = Lib::router($target);

        $base = $external ? Config::getBaseUrl() . '/' . Config::getBaseFolder() . '/' : '';

        if (!$router) {
            foreach ($options as $k => $v) {
                $values[] = $k . '=' . $v;
            }

            $queries = implode('&', $values);

            if (!empty($queries)) {
                $queries = '?' . $queries;
            }

            return $base . $target . '.php' . $queries;
        }

        $link = $base . $router->build($options);

        return $link;
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

    public static function cookie()
    {
        static $loaded;

        if (empty($loaded)) {
            require_once(dirname(__FILE__) . '/cookie.php');

            $loaded = true;
        }

        return Cookie::init();
    }

    public static function escape($output, $fromUrl = false)
    {
        $string = htmlspecialchars($output, ENT_COMPAT, 'UTF-8');

        if ($fromUrl) {
            $string = urlencode($string);
        }

        return $string;
    }
}

// Initiate session first
Lib::session();