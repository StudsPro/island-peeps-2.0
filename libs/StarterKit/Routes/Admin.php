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
	
	public function user_settings()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		parent::render('user_settings.twig',$args);
	}
	
	public function social_settings()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		parent::render('social_settings.twig',$args);
	}
	
	
	
	public function masterlist()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		$type_id = $cat_id = $sort = false;
		
		if(isset($get['type_id'])){
			$args['type_id'] = $type_id = (int) $get['type_id'];
		}
		if(isset($get['cat_id'])){
			$args['cat_id'] = $cat_id = (int) $get['cat_id'];
			$args['cat_count'] = $db->count('masterlist',' category_id=? ',[$cat_id]);
			$args['cat'] = $db->catById($cat_id);
		}
		if(isset($get['sort'])){
			$args['sort'] = $sort = $get['sort'];
		}
		
		$args['scripts'] = [
			'js/tooltip/tooltip.js',
			'js/plugins/datatables/jquery.dataTables.min.js',
			'js/demo/dataTables.bootstrap.js',
			'js/demo/tables.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'js/tooltip/tooltip.css'
		];
		
		$args['count'] = [
			'profile'=>$db->count('masterlist',' type_id="1" '),
			'meme'=>$db->count('masterlist',' type_id="2" '),
			'funfacts'=>$db->count('masterlist',' type_id="3" ')
		];
		
		$args['categories'] = $db->getAll('SELECT * FROM category');
		
		$args['masterlist'] = $db->getMasterList($type_id,$cat_id,$sort);

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
			case 'profile':
				$template = 'crud_profile.twig';
				$args['action'] = 'Create People Profile';
				$args['categories'] = $db->getAll('SELECT * FROM category');
				$args['regions'] = $db->getAll('SELECT * FROM country');
			break;
			case 'meme':
			
			break;
			case 'funfact':
			
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
	
	public function edit()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		$t = isset($get['t']) ? $get['t'] : false;
		$id = isset($get['id']) ? $get['id'] : false;
		if(empty($id)){
			$app->pass();
		}
		switch($t){
			case 'profile':
				$template = 'crud_profile.twig';
				$args['action'] = 'Create People Profile';
				$args['categories'] = $db->getAll('SELECT * FROM category');
				$args['regions'] = $db->getAll('SELECT * FROM country');
				$args['item'] = $db->getPeopleProfile($id);
			break;
			case 'meme':
			
			break;
			case 'funfact':
			
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
			'js/demo/dataTables.bootstrap.js',
			'js/demo/tables.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'js/tooltip/tooltip.css'
		];
		
		$args['countries'] = $db->getAll('SELECT * FROM country WHERE 1');

		parent::render('countries.twig',$args);
	}
	
	public function preview()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		$type_id = $get['type_id'];
		$id = $get['id'];
		
		switch($type_id){
			case 1:
				$args['profile'] = $db->getPeopleProfile($id);
				parent::render('partials/profile.twig',$args);
			break;
		}
	}
	
	public function categories()
	{
		
	}
	
	public function affiliates()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		$args['scripts'] = [
			'js/tooltip/tooltip.js',
			'js/plugins/datatables/jquery.dataTables.min.js',
			'js/demo/dataTables.bootstrap.js',
			'js/demo/tables.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'js/tooltip/tooltip.css'
		];
		$args['affiliates'] = $db->getAll('SELECT * FROM admin WHERE 1');
		parent::render('affiliates.twig',$args);
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
	