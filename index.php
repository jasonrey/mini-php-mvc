<?php
!defined('SERVER_EXEC') && define('SERVER_EXEC', true);

require_once(dirname(__FILE__) . '/lib/lib.php');

$db = Lib::db();

$db->query('select ??, ?? from ?? as ??', ['id', 't`', 'a', 'apple']);

var_dump($db->fetchAll());

// Lib::route();
