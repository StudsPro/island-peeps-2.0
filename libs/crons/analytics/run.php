<?php

date_default_timezone_set('UTC');

require __DIR__ . '/../../cron_db_connection.php';

require 'GAPI.php'; //google analytics api class

$ga = new GoogleAnalyticsAPI('service');
$ga->auth->setClientId('444232255351-jp55p2lohhfu4oao0bpm0lggo00sotuc.apps.googleusercontent.com'); // From the APIs console
$ga->auth->setEmail('444232255351-jp55p2lohhfu4oao0bpm0lggo00sotuc@developer.gserviceaccount.com'); // From the APIs console
$ga->auth->setPrivateKey(__DIR__ . '/secret.p12');

$auth = $ga->auth->getAccessToken();

if ($auth['http_code'] != 200) {
	print_r($auth);
	throw new \exception('Fail to connect');
}   
 
$token = $auth['access_token'];
$expires = $auth['expires_in'];
$created = time();

$ga->setAccessToken($token);
$ga->setAccountId('ga:109638630');


//set some common dates
$now = strtotime( date('Y-m-d') . '-1 day'); 

// $dates represents the start date parameter for each timespan. no need to specify end date, we already have it in $now
$dates = [
	'year'=> strtotime( $now.' -1 year'),
	'month'=> strtotime( $now.' -1 month'),
	'day'=> strtotime( $now.' -1 day')
];

$data = [];


//month hits

//year hits

//device 
$data['devices'] = $ga->getVisitsBySystemOs(['max-results' => 100]);

//visits by location
$data['locations'] = $ga->query([
    'metrics' => 'ga:visits',
    'dimensions' => 'ga:country',
    'sort' => '-ga:visits',
    'max-results' => 50,
    'start-date' => $dates['year']
]);

//now we are ready to query the api and store data to the database.