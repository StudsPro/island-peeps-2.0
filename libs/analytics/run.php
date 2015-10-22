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
$now = date('Y-m-d',strtotime( date('Y-m-d') . '-1 day')); 

// $dates represents the start date parameter for each timespan. no need to specify end date, we already have it in $now
$dates = [
	'year'=> date("Y-m-d", strtotime('first day of January '.date('Y') )),
	'month'=> date('Y-m-01',strtotime('this month')),
	'day'=> date('Y-m-d',strtotime( $now.' -1 day'))
];

$data = [];

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

//new vs returning
$data['new_vs_returning'] = $ga->query([
    'metrics' => 'ga:sessions',
    'dimensions' => 'ga:userType',
]);

//visits by city
$data['visits_by_city'] = $ga->query([
    'metrics' => 'ga:sessions',
    'dimensions' => 'ga:city',
	'max-results'=> 10,
]);

//map view
$data['map_view'] = $ga->query([
    'metrics' => 'ga:sessions',
    'dimensions' => 'ga:city,ga:Latitude,ga:Longitude',
]);



//visitors by browser
$data['browser'] = $ga->query([
	'metrics'=> 'ga:sessions',
	'dimensions' => 'ga:browser'
]);


//visitors by screensize
$data['screen_sizes'] = $ga->query([
	'metrics'=> 'ga:sessions',
	'dimensions' => 'ga:screenResolution',
]);


//visits by service provider
$data['isp'] = $ga->query([
	'metrics'=> 'ga:sessions',
	'dimensions' => 'ga:networkDomain',
	'max-results' => 50
]);

//most popular pages
$data['browser'] = $ga->query([
	'metrics'=> 'ga:pageViews',
	'dimensions' => 'ga:pagePath',
	'max-results'=>10
]);


//failing queries
$data['social'] = $ga->query([
	'metrics'=> 'ga:socialActivies',
	'dimensions' => 'ga:socialActivityPost,ga:socialActivityContentUrl',
	'max-results'=>20
]);

$data['hits_by_day'] = $ga->query([
	'metrics'=> 'ga:pageviews,ga:visitors',
	'dimensions' => 'ga:date',
	'start-date' => $dates['month'],
	'end-date'=> $now
]);

$data['this_month'] = [
	'name'=>date('M'),
	'data'=>$ga->query([
		'metrics' => 'ga:visits',
		'dimensions' => 'ga:date',
		'start-date' => $dates['month'],
		'end-date'=> $now
	])
];

$data['this_year'] = [
	'name'=>date('Y'),
	'data'=>$ga->query([
		'metrics' => 'ga:visits',
		'dimensions' => 'ga:date',
		'start-date' => $dates['year'],
		'end-date'=> $now
	])
];



$t = $db->model('analytics');
$t->timestamp = time();
foreach($data as $k => $v)
{
	$t->{$k} = json_encode($v);
}
$db->store($t);