# mini-php-mvc
A minimal PHP MVC for personal use.

# Usage

    !defined('SERVER_EXEC') && define('SERVER_EXEC', 1);

    require_once('lib/base.php');

# DB

    (DB) Lib::db();

- `DB` is an alias to the `mysqli` class.

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

# Template

    $view = Lib::view($viewName);
    
    (string) $view->output($templateName = 'index');

- Templates reside in `root/templates/{viewName}` folder
- File name is `{templateName}.php`
- Templates automatically extends the `view` class so methods that are defined in `{viewName}View` are available in templates

# Ajax

    (Ajax) Lib::ajax();

- Ajax has `success` and `fail` method that helps with generating the appropriate json response

## Ajax::success

    (object) $ajax->success($message = '');

_Result_

    {
        "status": "success",
        "response": Mixed
    }

- `$message` is empty string by default by accepts object and array as well.
- `result.status` is `"success"`
- `result.response` can be string, integer, or json encoded array/object

## Ajax::fail

    (object) $ajax->fail($message = '');

_Result_

    {
        "status": "fail",
        "response": Mixed
    }

- `$message` is empty string by default by accepts object and array as well.
- `result.status` is `"fail"`
- `result.response` can be string, integer, or json encoded array/object

## Client side

    $.ajax(namespace, data, function(result) {
        // result.status
        // result.response
    });