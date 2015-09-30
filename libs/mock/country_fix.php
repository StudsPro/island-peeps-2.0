<?php

require '../cron_db_connection.php';

$c = $db->findAll('country');

foreach($c as &$t)
{
	$t->uri = strtolower(url_safe($t->name));
	$db->store($t);
}

function url_safe($title)
{
	$title = preg_replace('/[^A-Za-z 0-9]/','',$title);
	$title = preg_replace('/[\t\n\r\0\x0B]/', '', $title);
	$title = preg_replace('/([\s])\1+/', ' ', $title);
	$title = trim($title);
	$title = str_replace(' ','-',$title);
	return $title;
}