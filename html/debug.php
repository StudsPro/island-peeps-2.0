<?php

define( 'LIB_PATH' , realpath( __DIR__ . '/../libs' ).'/'  );

$libs = [
	'vendor/autoload.php',
	'StarterKit/App.php',
	'config.php'
];

try{
	
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
	
	require LIB_PATH . 'routes_debug.php';
	
	$app->run();	
	
}
catch(exception $e){
	
	if($config['debug']){
		echo json_encode(['error'=>1,'message'=>'app failed to run with message '.$e->getMessage().' in '.$e->getFile().' at line '.$e->getLine()]);	
	}
	
}