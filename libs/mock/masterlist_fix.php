<?php

require '../cron_db_connection.php';

require '../vendor/autoload.php';

$x = $db->findAll('masterlist',' type_id IN(1,3) ');

foreach($x as $y){
	$y->uri = strtolower(url_safe($y->title));
	$db->store($y);
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