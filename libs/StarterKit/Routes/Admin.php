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
			
				if(\StarterKit\Admin::restore($_COOKIE['__restore']) === true){
					if(!$this->app->is_admin()){
						$this->app->session['admin'] = $this->app->args['admin'] = $_SESSION['admin'];
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
	
	public function dashboard()
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

		parent::render('dashboard.twig',$args);
	}
	
	public function masterlist()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		
		$args['scripts'] = [
			'js/tooltip/tooltip.js',
			'js/plugins/datatables/jquery.dataTables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'js/tooltip/tooltip.css',
			'js/plugins/datatables/css/jquery.dataTables.css'
		];
		
		$args['categories'] = $db->getAll('SELECT * FROM category');
		
		$args['masterlist'] = $db->getMasterList();

		parent::render('masterlist.twig',$args);
	}
	
	public function create()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		$t = isset($get['t']) ? $get['t'] : false;
		switch($t){
			case 'masterlist':
				$template = 'crud_masterlist.twig';
				$args['action'] = 'Create Profile';
			break;
			case 'country':
				$template = 'crud_country.twig';
				$args['action'] = 'Create Country';
			break;
			case false:
			default:
				$app->pass();
			break;
		}
		parent::render($template,$args);
	}
	
	public function countries()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		
		$args['scripts'] = [
			'js/tooltip/tooltip.js',
			'js/plugins/datatables/jquery.dataTables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'js/tooltip/tooltip.css',
			'js/plugins/datatables/css/jquery.dataTables.css'
		];
		
		$args['countries'] = $db->getAll('SELECT * FROM country WHERE 1');

		parent::render('countries.twig',$args);
	}
	
	public function categories()
	{
		
	}
	
	public function affiliates()
	{
		
	}
	
	public function login()
	{
		$app  = $this->app;
		$args = $app->args;
		if($app->is_admin()){
			$app->redirect('/admin/dashboard');
		}
		parent::render('login.twig',$args);
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
	