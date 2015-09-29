<?php

require '../cron_db_connection.php';

require '../vendor/autoload.php';

$filter = \RedBeanFVM\RedBeanFVM::getInstance();

$key_map = [
	'title'=>'title',
	'memefile'=>'img',
	'status'=>'status'
];

$c = array(
  array('id' => '5','title' => 'MEME1','memefile' => '14098302631393452702Belize-Vacation-Resort.jpg','status' => '1'),
  array('id' => '6','title' => 'MEME2','memefile' => '14098303381393454969beautiful-panama-beach-wallpapers-2560x1920.jpg','status' => '1'),
  array('id' => '7','title' => 'Mash Mout','memefile' => '1414961283IMG0019.jpg','status' => '1'),
  array('id' => '8','title' => 'Kick Awf Face','memefile' => '1414961567IMG0015.jpg','status' => '1'),
  array('id' => '9','title' => 'bad gyal','memefile' => '1414961740IMG0009.jpg','status' => '1'),
  array('id' => '10','title' => 'Tested vip','memefile' => '1432878260DumbWaiter.jpg','status' => '1'),
  array('id' => '11','title' => 'Debi Nova','memefile' => '1433000162Debi-Nova2.jpg','status' => '1')
);

$data = [];

foreach($c as $row)
{
	$tmp = [];
	foreach($key_map as $old => $new)
	{
		$tmp[$new] = $row[$old];
	}
	$data[] = $tmp;
}

foreach($data as $row)
{
	$t = $db->model('masterlist');
	foreach($row as $k=>$v){
		$t->{$k} = $v;
	}
	$t->type_id = 2;
	$t->uri = '/meme/'.strtolower(url_safe($t->title));
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