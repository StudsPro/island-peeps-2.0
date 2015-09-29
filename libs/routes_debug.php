<?php

$app->group('/debug',function() use($app){
	
	$app->map('/:fn',function($fn){
		(new \StarterKit\Routes\Debug)->{$fn}();
	})->via('GET','POST');
	
});