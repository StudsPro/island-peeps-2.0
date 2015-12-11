<?php

require '../cron_db_connection.php';

$x = $db->getAll('SELECT id FROM country WHERE 1');

$zs = ['flag_img','img','cover_img','map_img'];
foreach($x as $row){
	$y = $db->model('country',$row['id']);
	foreach($zs as $z){
		$w = explode('/',$y->{$z});
		$y->{$z} = array_pop($w);	
	}
	$db->store($y);	
}