<?php

require __DIR__ .'/../cron_db_connection.php';

$xs = ['Forget Password','Suggestion','Suggestion Deny','Suggestion Accept','Change Affiliate Password'];

foreach($xs as $x)
{
	$t = $db->model('mailtemplate');
	$t->title      = $x;
	$t->subject    ='not set';
	$t->from_email = 'admin@islandpeeps.openex.info';
	$t->html = '';
	$db->store($t);
}