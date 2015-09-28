<?php

require '../cron_db_connection.php';

require '../vendor/autoload.php';

$filter = \RedBeanFVM\RedBeanFVM::getInstance();

$a = $db->model('admin');

$a->name = 'Studs';
$a->email = 'derrickfrancis@me.com';
$a->password = $filter->password_hash('password');
$a->sidebar = 0;
$a->order = '';

$db->store($a);