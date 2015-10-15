<?php

require_once '../cron_db_connection.php';

$items = [
	'home','map','suggestion','haiti','jamaica','puerto rico','trinidad','stats','about'
];

foreach($items as $i)
{
	$t = $db->model('slide');
	$t->title = $i;
	$t->image = '';
	$t->video = '';
	$db->store($t);
}