<?php

namespace StarterKit\Routes;

class Error extends ViewController
{
	function __construct()
	{
		$this->app = \StarterKit\App::getInstance();
		parent::__construct();
	}
	
	public function run($code)
	{
		$args = $this->app->args;
		switch($code){
			case 404:
				$args['title'] = '404 Not Found';
			break;
			case 500:
				$args['title'] = '500 Internal Server Error';
			break;
			default:
				throw new \exception('illegal error code.');
			break;
		}
		$args['e_code'] = $code;
		parent::render('error.twig',$args);
	}
}