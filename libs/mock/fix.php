<?php

require '../cron_db_connection.php';

$ts = $db->findAll('masterlist');

foreach($ts as $t)
{
	$t->updated = time();
	$db->store($t);
}
