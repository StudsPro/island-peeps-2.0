<?php

$config = [

	'base_url'=>'http://islandpeeps.openex.info/',
	
	'public' => '/usr/share/nginx4/html/',

    'db_args' => [
        'user'=>'islandpeeps',
        'pass'=>'HfWsq5Hf4HWnHzcZ',
        'host'=>'127.0.0.1',
        'name'=>'islandpeeps'
    ],
	
	'wowload'=> false, //whether or not to display animations.

    'debug'=> true,

    'strict' => true,
	
	'minify' => false, //use minified css/js

    'scheme' => 'http://',

    'slim_args' => [
		'debug'=>true,//catch errors that occur inside of the slim instance
    ],

    'twig_args' => [
		'template_path'=>__DIR__ .'/StarterKit/Views/' //path to the template directory
    ],

    'timezone' => 'America/New_York',

    'session_args' => [
		'name'=>'sk_id',//name of session
		'type'=>'redis', //what type of session storage to use.
		'host'=>'127.0.0.1', //when scaling to multi server install, this will be used to connect to redis or other session store
		'port'=>6379  //same 
    ],

    'template_args' => [
        'title'=>'IslandPeeps',
        'meta_description'=>'',
        'keywords'=>'',
        'site_name'=>'IslandPeeps',
    ],
	
	'cache_args' => [
		'enabled'=>false,
		'servers'=>[
			['127.0.0.1',11211]
		]
	],
	
	'smtp_args' => [
		'enabled'=>false,
        'host'=>'smtp.sendgrid.net',
        'port'=>587,
        'user'=>'',
        'pass'=>'',
        'send_from'=>'',
        'admin'=>'',
		'template_path'=>__DIR__ .'/StarterKit/Emails/',
    ],

];