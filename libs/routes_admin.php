<?php

$app->get('/',function() use($app){
	$app->pass();
});

//v1 of the api
$app->group('/admin',function() use($app){
	
	$app->get('/',function() use($app){
		$app->redirect('/admin/dashboard');
	});
	
	$app->get('/:fn',function($fn){
		(new \StarterKit\Routes\Admin)->run($fn);
	});
	
	$app->map('/api/:method',function($method){
		(new \StarterKit\Routes\AdminAPI)->__try($method);
	})->via('GET','POST');
	
});

$app->error(function (exception $e){
	(new \StarterKit\Routes\Error)->run(500);
});

$app->notFound(function(){ 
	(new \StarterKit\Routes\Error)->run(404);
});
