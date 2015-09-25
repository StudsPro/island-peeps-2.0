<?php

$app->get('/',function() use($app){
	$app->pass();
});

//v1 of the api
$app->group('/admin',function() use($app){
	
	$app->get('/:page',function($page){
		(new \StarterKit\Routes\Admin)->{$page}();
	});
	
	$app->get('/edit/:thing/:id',function($thing,$id){
		(new \StarterKit\Routes\Admin)->edit($thing,$id);
	});
	
	$app->map('/api/:method',function($method){
		(new \StarterKit\Routes\API)->__try($method);
	})->via('GET','POST');
	
});

$app->error(function (exception $e){
	(new \StarterKit\Routes\Error)->run(500);
});

$app->notFound(function(){ 
	(new \StarterKit\Routes\Error)->run(404);
});
