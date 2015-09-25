<?php

$config = [

	'base_url'=>'http://wixtape.com/',
	
	'public' => '/usr/share/nginx/html/',

    'db_args' => [
        'user'=>'webserver',
        'pass'=>'ppc2VzF8uexUESdn',
        'host'=>'45.63.4.96',
        'name'=>'wixtape'
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
		'host'=>'45.63.13.64', //when scaling to multi server install, this will be used to connect to redis or other session store
		'port'=>6379  //same 
    ],

    'template_args' => [
        'title'=>'Wixtape',
        'meta_description'=>'',
        'keywords'=>'',
        'sitename'=>'Wixtape',
    ],

	'ratelimit_args' => [
		'enabled'=>false,
		'limit'=>120,
		'host'=>'45.63.13.64',
		'port'=>6379
	],
	
	'cache_args' => [
		'servers'=>[
			['104.238.135.29',11211]
		]
	],
	
	'aws_args' =>[
		'config'=>[
			'version'=> 'latest',
			'region'=> 'us-east-1',
			'credentials' => [
				'key'    => 'AKIAJ4EOB2G7CZX553ZA',
				'secret' => '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'
			],
			'debug'=>false		
		],
		'mp3bucket'=>'audio1.mixtape',
		'imgbucket'=>'img1.mixtape',
		'zipbucket'=>'zip1.mixtape'
	],
	
	'smtp_args' => [
        'host'=>'smtp.sendgrid.net',
        'port'=>587,
        'user'=>'wixtape.com',
        'pass'=>'45%nf0xdd,xor8te falls',
        'send_from'=>'noreply@wixtape.com',
        'admin'=>'',
		'template_path'=>__DIR__ .'/StarterKit/Emails/',
    ],

];