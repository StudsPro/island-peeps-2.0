<?php

namespace StarterKit\Routes;

class Front extends \StarterKit\Routes\ViewController
{
	public $app;
	function __construct()
	{
		$this->app = (\StarterKit\App::getInstance());
		parent::__construct();
	}
	
	function index()
	{
		parent::render('frontend/index.twig',$this->app->args);
	}
}