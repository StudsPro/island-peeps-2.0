<?php

$app->group('/debug',function() use($app){
	
	$app->get('/:fn',function($fn){
		(new \StarterKit\Routes\Debug)->{$fn}();
	});
	
});