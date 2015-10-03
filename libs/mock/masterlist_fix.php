<?php

require '../cron_db_connection.php';

require '../vendor/autoload.php';


$filter = \RedBeanFVM\RedBeanFVM::getInstance();

$filter->custom_filter('ytube',function($input){
	$input = preg_replace('~
		https?://         # Required scheme. Either http or https.
		(?:[0-9A-Z-]+\.)? # Optional subdomain.
		(?:               # Group host alternatives.
		  youtu\.be/      # Either youtu.be,
		| youtube         # or youtube.com or
		  (?:-nocookie)?  # youtube-nocookie.com
		  \.com           # followed by
		  \S*             # Allow anything up to VIDEO_ID,
		  [^\w\s-]       # but char before ID is non-ID char.
		)                 # End host alternatives.
		([\w-]{11})      # $1: VIDEO_ID is exactly 11 chars.
		(?=[^\w-]|$)     # Assert next char is non-ID or EOS.
		(?!               # Assert URL is not pre-linked.
		  [?=&+%\w.-]*    # Allow URL (query) remainder.
		  (?:             # Group pre-linked alternatives.
			[\'"][^<>]*>  # Either inside a start tag,
		  | </a>          # or inside <a> element text contents.
		  )               # End recognized pre-linked alts.
		)                 # End negative lookahead assertion.
		[?=&+%\w.-]*        # Consume any URL (query) remainder.
		~ix', 
		'$1',
	$input);
	return $input;
});

$x = $db->findAll('masterlist',' youtube IS NOT NULL');

foreach($x as &$y){
	$y->youtube = $filter->ytube($y->youtube);
	$db->store($y);
}



/*
fix birthdays
$x = $db->findAll('masterlist',' type_id="1" AND birthday IS NOT NULL');

foreach($x as &$y){
	$date = explode('-',$y->birthday);
	$y->year = $date[0];
	$y->month = $date[1];
	$y->day   = $date[2];
	$db->store($y);
}
*/