<?php

require '../cron_db_connection.php';

foreach(['People Profile','Meme','Fun Fact'] as $it)
{
	$t = $db->model('type');
	$t->name = $it;
	$db->store($t);
}