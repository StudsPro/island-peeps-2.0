<?php

//provides a database connection for cron jobs.

require 'config.php';
require 'vendor/autoload.php';
require 'StarterKit/App.php';

\StarterKit\App::registerAutoloader();

$db = \StarterKit\DB::getInstance($config['db_args']);

$cache = \StarterKit\Cache::getInstance($config['cache_args']);
