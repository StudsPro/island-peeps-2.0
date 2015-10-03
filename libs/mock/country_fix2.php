<?php


require '../cron_db_connection.php';

require '../vendor/autoload.php';


$filter = \RedBeanFVM\RedBeanFVM::getInstance();


$x = $db->findAll('country',' independence IS NOT NULL');

foreach($x as &$y){
	$date = explode('-',$y->independence);
	$y->year = $date[0];
	$y->month = $date[1];
	$y->day   = $date[2];
	$db->store($y);
}