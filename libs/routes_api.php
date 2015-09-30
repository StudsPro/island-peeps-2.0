<?php

$app->get('/',function() use($app){
	$app->pass();
});

//v1 of the api
$app->group('/api/v1',function() use($app){
	
	$app->map('/:method',function($method){
		(new \StarterKit\Routes\API)->__try($method);
	})->via('GET','POST');
	
});

$app->error(function (exception $e){
	(new \StarterKit\Routes\Error)->run(500);
});

$app->notFound(function(){ 
	echo json_encode(['error'=>1,'message'=>'404 Not Found']);
});
