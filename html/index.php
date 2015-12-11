<?php

define( 'LIB_PATH' , realpath( __DIR__ . '/../libs' ).'/'  );

$libs = [
	'vendor/autoload.php',
	'StarterKit/App.php',
	'config.php'
];
	
foreach($libs as $lib){
	require LIB_PATH . $lib;
}

\StarterKit\App::registerAutoloader();

$app = \StarterKit\App::getInstance($config);

$app->hook('slim.before',function() use($app){
	$app->__before();
});

$app->hook('slim.before.router',function() use($app){
	$app->__beforeRouter();
});

$app->hook('slim.before.dispatch',function() use($app){
	$app->__beforeDispatch();
});

$app->hook('slim.after',function() use($app){
	$app->__after();
});

//v1 of the api
$app->group('/api/v1',function() use($app){
	
	$app->map('/:method',function($method){
		(new \StarterKit\Routes\API)->__try($method);
	})->via('GET','POST');
	
	$app->error(function (exception $e){
		echo json_encode(['error'=>1,'message'=>'500 Internal Server Error']);
	});

	$app->notFound(function(){ 
		echo json_encode(['error'=>1,'message'=>'404 Not Found']);
	});
	
});

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

$app->group('/debug',function() use($app){

	$app->map('/:fn',function($fn){
		(new \StarterKit\Routes\Debug)->{$fn}();
	})->via('GET','POST');
	
});

$app->get('/.*',function(){
	(new \StarterKit\Routes\Front)->index();
});

$app->error(function (exception $e){
	(new \StarterKit\Routes\Error)->run(500);
});

$app->notFound(function(){ 
	(new \StarterKit\Routes\Error)->run(404);
});

$app->run();	