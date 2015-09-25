<?php

namespace StarterKit\Routes;

class Admin extends ViewController
{
	public $app;
	function __construct()
	{
		$this->app = (\StarterKit\App::getInstance());
		parent::__construct();
	}
	
	public function run($fn)
	{
		if(!method_exists($this,$fn)){
			$this->app->pass();
		}
		
		if(isset($_COOKIE['__restore'])){
			
			if(!$this->app->is_admin()){
			
				if(\StarterKit\User::restore($_COOKIE['__restore']) === true){
					if(!$this->app->is_admin()){
						$this->session['admin'] = $this->args['admin'] = $_SESSION['admin'];
					}
				}
				
			}
		
		}
		
		if(!$this->app->is_admin() && $fn !== 'login' ){
			$this->app->redirect('/admin/login',302);
		}else{
			call_user_func([$this,$fn]);
		}
	}
	
	public function index()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		
		$args['scripts'] = [
			'plugins/flot/jquery.flot.js',
			'plugins/flot/jquery.flot.selection.js',
			'plugins/jqvmap/jquery.vmap.js',
			'plugins/jqvmap/maps/jquery.vmap.world.js',
			'plugins/jqvmap/data/jquery.vmap.sampledata.js',
			'plugins/easy-pie-chart/jquery.easypiechart.min.js',
			'plugins/jquery.sparkline/jquery.sparkline.min.js',
			'plugins/fullcalendar/fullcalendar.min.js',
			'plugins/justgage/lib/raphael.2.1.0.min.js',
			'plugins/justgage/justgage.js',
			'plugins/gmaps/gmaps.js'
		];
		
		$args['scripts_external'] = '//maps.google.com/maps/api/js?sensor=true';

		parent::render('admin_index.twig',$args);
	}
	
	
	public function login()
	{
		$app  = $this->app;
		$args = $app->args;
		parent::render('admin_login.twig',$args);
	}
	
	public function logout()
	{
		$app = $this->app;
		if($app->is_admin()){
			$app->session['admin']->logout();
		}
		$app->redirect('/?admin_logged_out');
	}
	
}
	