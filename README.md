# mini-php-mvc
A minimal PHP MVC for personal use.

# Usage

    !defined('SERVER_EXEC') && define('SERVER_EXEC', 1);

    require_once('lib/lib.php');

# DB

    (DB) Lib::db($key = 'default');

- `DB` is an alias to the `mysqli` class
- `$key` specify which database to connect to, reflected in `config.php`

# Model

    (Model) Lib::model($name);

- Models reside in `root/models` folder
- File name is `{name}.php`
- Class name is `{Name}Model` that extends `Model` class
- `{name}` also correlates to `{tablename}`

## Table binding

    (array(Table)) $model->query($sql);

- Regardless of the number of result, return is always array
- Auto binds result to the correlated table

# Table

    (Table) Lib::table($name);

- Tables reside in `root/tables` folder
- Need to define columns as instance propertes except `id` (automatically defined)
- File name must follow table name, `{name}.php`
- Class name is `{Name}Table` that extends `Table` class

# View

    (View) Lib::view($name);

- Views reside in `root/views` folder
- View's file name is `{name}.php`
- View's class name is `{Name}View` that extends `View` class

# Routers

    (Router) Lib::router($name);

- Routers reside in `root/routers` folder
- Router's file name is `{name}.php`
- Router's class name is `{Name}Router` that extends `Router` class
- Router should mainly have properties of `$allowedRoute`, `$allowedBuild` and `$segments`
- Router have 2 methods for overriding: `decode` and `encode`
- `$allowedRoute` specifies the first segment of an URL for the router to process
- `$allowedBuild` specifies the allowed keys to build a URL
- `$segments` specifies the order and key name of a `GET` value

# Template

    $view = Lib::view($viewName);

    (string) $view->output($templateName = 'index');

- Templates reside in `root/templates/{viewName}` folder
- File name is `{templateName}.php`
- Templates automatically extends the `view` class so methods that are defined in `{viewName}View` are available in templates

# API/Ajax

    (Api) Lib::api();

- Api has `success` and `fail` method that helps with generating the appropriate json response

## Api->success

    (object) $api->success($data = '');

_Result_

    {
        "state": true,
        "status": "success",
        "data": $data
    }

- `$data` is empty string by default by accepts object and array as well
- `result.state` is `true`
- `result.status` is `"success"`
- `result.data` is the data returned by server

## Api->fail

    (object) $api->fail($data = '');

_Result_

    {
        "state": false,
        "status": "fail",
        "data": $data
    }

- `$data` is empty string by default by accepts object and array as well
- `result.state` is `false`
- `result.status` is `"fail"`
- `result.data` is the data returned by server

## Client side

    var callback = function(response) {
        // response.state
        // response.status
        // response.data
    };

## API Files

    apis/{subject}.php

- All API/AJAX calls resides in root/apis folder
- Segregate by subject
- Call with `api/{subject}/{action}`
- `{action}` will be the method name

# Req

- An independent class to handle `$_GET` and `$_POST` inputs

## Req::get

    (mixed) Req::get($key = null, $default = null);

- If `$key` is null, returns the whole `$_GET` array

## Req::post

    (mixed) Req::post($key = null, $default = null);

- If `$key` is null, returns the whole `$_POST` array

## Req::hasget

    (boolean) Req::hasget($key);

## Req::haspost

    (boolean) Req::haspost($key);

## Req::set

    (null) Req::set($type, $key, $value);

- `$type` denotes either GET or POST to set

# Session

    (Session) Lib::session();

## Session->id

    (string) $session->id;

## Session->get

    (mixed) $session->get($key, $default = null);

## Session->set

    (null) $session->set($key, $value);

# Cookie

    (Cookie) Lib::cookie();

## Cookie->get

    (mixed) $cookie->get($key, $default = null);

## Cookie->set

    (null) $cookie->set($key, $value);
