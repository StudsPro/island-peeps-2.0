<?php

require '../cron_db_connection.php';

require '../vendor/autoload.php';


$filter = \RedBeanFVM\RedBeanFVM::getInstance();

$x = $db->findAll('masterlist');

foreach($x as &$y){
	if(strpos('/',$y->img) !== false){
		$y->img = array_pop(explode('/',$y->img));
		$db->store($y);	
	}
}

$x = $db->findAll('slide');

foreach($x as &$y){
	if(!empty($y->image)){
		$y->image = array_pop(explode('/',$y->image));
	}
	if(!empty($y->video)){
		$y->video = array_pop(explode('/',$y->video));
	}
	$db->store($y);
}

$x = $db->findAll('country');

$zs = ['flag_img','img','cover_img','map_img'];
foreach($x as &$y){
	foreach($zs as $z){
		if(strpos('/',$y->{$z}) !== false){
			$y->{$z} = array_pop(explode('/',$y->{$z}));
		}	
	}
	$db->store($y);	
}