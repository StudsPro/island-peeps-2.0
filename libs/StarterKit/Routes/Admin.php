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
					$this->app->session['admin'] = $this->app->args['admin'] = $_SESSION['admin'];
				}
				
			}
		}
		
		$this->app->args['sidebar_calendar_count'] = $this->app->db->todayCountCalendar();
		$this->app->args['marquee_message'] = $this->app->db->getCell('SELECT marquee_message FROM sitesetting WHERE id="1"');
		if(!$this->app->is_admin() && $fn !== 'login' ){
			$this->app->redirect('/admin/login',302);
		}else{
			if($this->app->is_admin()){
				$this->app->args['nfeed'] = $this->app->db->getNfeed($this->app->session['admin']->id);
			}
			call_user_func([$this,$fn]);
		}
	}
	
	public function unauthorized()
	{
		parent::render('unauthorized.twig',$this->app->args);
	}
	
	public function dashboard()
	{
		$app  = $this->app;
		$args = $app->args;
		$admin = $app->session['admin'];
		
		
		if($admin->can('dashboard','view')){
			
			$args['scripts'] = [
				'js/amcharts/raphael.js',
				'js/amcharts/amcharts.js',
				//'plugins/flot/jquery.flot.js',
				//'plugins/flot/jquery.flot.selection.js',
				'js/plugins/jqvmap/jquery.vmap.js',
				'js/plugins/jqvmap/maps/jquery.vmap.world.js',
				//'plugins/jqvmap/data/jquery.vmap.sampledata.js',
				//'plugins/easy-pie-chart/jquery.easypiechart.min.js',
				//'plugins/jquery.sparkline/jquery.sparkline.min.js',
				'js/plugins/fullcalendar/fullcalendar.min.js',
				//'plugins/justgage/lib/raphael.2.1.0.min.js',
				'plugins/justgage/justgage.js',
				'plugins/gmaps/gmaps.js',
				'js/dashboard.js',
				'js/calendar.js'
			];
			
			$args['scripts_external'] = [
				'//maps.google.com/maps/api/js?sensor=true'
			];
			
			$args['styles'] = [
				'js/plugins/jqvmap/jqvmap.css',
				'css/plugins/fullcalendar/fullcalendar.css'
			];
		
		}else{
			$args['unauthorized'] =  true;
		}

		parent::render('dashboard.twig',$args);
	}
	
	public function stats()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		if(!$app->session['admin']->can('stats','view')){
			$app->redirect('/admin/unauthorized');
		}
		
		$args['scripts'] = [
			'js/amcharts/raphael.js',
			'js/amcharts/amcharts.js',
			//'plugins/flot/jquery.flot.js',
			//'plugins/flot/jquery.flot.selection.js',
			'js/plugins/jqvmap/jquery.vmap.js',
			'js/plugins/jqvmap/maps/jquery.vmap.world.js',
			//'plugins/jqvmap/data/jquery.vmap.sampledata.js',
			//'plugins/easy-pie-chart/jquery.easypiechart.min.js',
			//'plugins/jquery.sparkline/jquery.sparkline.min.js',
			'js/plugins/fullcalendar/fullcalendar.min.js',
			//'plugins/justgage/lib/raphael.2.1.0.min.js',
			'plugins/justgage/justgage.js',
		];

		parent::render('stats.twig',$args);
	}
	
	public function masterlist_stats()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		if(!$app->session['admin']->can('masterlist','view_stats')){
			$app->redirect('/admin/unauthorized');
		}
		
		$args['scripts'] = [
			'js/amcharts/raphael.js',
			'js/amcharts/amcharts.js',
			//'plugins/flot/jquery.flot.js',
			//'plugins/flot/jquery.flot.selection.js',
			'js/plugins/jqvmap/jquery.vmap.js',
			'js/plugins/jqvmap/maps/jquery.vmap.world.js',
			//'plugins/jqvmap/data/jquery.vmap.sampledata.js',
			//'plugins/easy-pie-chart/jquery.easypiechart.min.js',
			//'plugins/jquery.sparkline/jquery.sparkline.min.js',
			'js/plugins/fullcalendar/fullcalendar.min.js',
			//'plugins/justgage/lib/raphael.2.1.0.min.js',
			'plugins/justgage/justgage.js',
		];

		parent::render('masterlist_stats.twig',$args);
	}
	
	public function calendar()
	{
		$app = $this->app;
		$args = $this->app->args;
		$get = $this->app->get;
		
		if(!$app->session['admin']->can('calendar','view')){
			$app->redirect('/admin/unauthorized');
		}
		$args['scripts'] = [
			'js/plugins/fullcalendar/fullcalendar.min.js',
			'js/calendar.js'
		];
		
		$args['styles'] = [
			'css/plugins/fullcalendar/fullcalendar.css'
		];
		
		if(isset($get['month'])){
			$args['start_month'] = (int) $get['month'];
		}else{
			$args['start_month'] = (int) date('m');
		}
		
		$args['dob_count'] = $this->app->db->dobCountAdded();
		
		parent::render('calendar.twig',$args);
	}
	
	public function user_settings()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		parent::render('user_settings.twig',$args);
	}
	
	public function site_settings()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		if(!$app->session['admin']->can('site','view')){
			$app->redirect('/admin/unauthorized');
		}
		$args['item'] = $db->getRow('SELECT * FROM sitesetting WHERE id="1"');
		$args['scripts'] = [
			'js/plugins/wysihtml5/wysihtml5-0.3.0.min.js',
			'js/plugins/bootstrap3-wysihtml5/bootstrap3-wysihtml5.all.min.js',
			'js/plugins/ckeditor/ckeditor.js',
			'js/plugins/marked/marked.js',
			'js/plugins/to-markdown/to-markdown.js',
			'js/plugins/bootstrap-markdown/bootstrap-markdown.js',
			'js/demo/ui-elements.js',
			'js/sitesettings.js'
		];
		parent::render('site_settings.twig',$args);
	}
	
	public function help()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		$args['help_content'] = $db->getCell('SELECT help_content from sitesetting WHERE id="1"');
		parent::render('help.twig',$args);
	}
	
	public function social_settings()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		if(!$app->session['admin']->can('social','view')){
			$app->redirect('/admin/unauthorized');
		}
		
		$args['styles'] = 'css/socialfeed.css';
		$args['settings'] = $db->socialSettings();
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
		}else{
			$args['sort'] = $sort = 'ABC';
		}
		
		$args['masterlist_help'] = $db->getCell('SELECT masterlist_help FROM sitesetting WHERE id="1"');
		
		
		$args['scripts'] = [
			'js/tooltip/tooltip.js',
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
			'js/tooltip/tooltip.css'
		];
		
		$args['count'] = [
			'profile'=>$db->count('masterlist',' type_id="1" '),
			'meme'=>$db->count('masterlist',' type_id="2" '),
			'funfacts'=>$db->count('masterlist',' type_id="3" ')
		];
		
		$args['categories'] = $db->getAll('SELECT * FROM category ORDER BY name ASC');
		
		$args['masterlist'] = $db->getMasterList($type_id,$cat_id,$sort);

		parent::render('masterlist.twig',$args);
	}
	
	public function create()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		$admin = $app->session['admin'];
		
		$t = isset($get['t']) ? $get['t'] : false;
		switch($t){
			case 'profile':
				$template = 'crud_profile.twig';
				$args['action'] = 'Create People Profile';
				$args['categories'] = $db->getAll('SELECT * FROM category ORDER BY name ASC');
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
				$module = 'masterlist';
			break;
			case 'meme':
				$template = 'crud_meme.twig';
				$args['action'] = 'Create Meme';
				$module = 'masterlist';
			break;
			case 'funfact':
				$template = 'crud_funfact.twig';
				$args['action'] = 'Create Fun Fact';
				$args['categories'] = $db->getAll('SELECT * FROM category ORDER BY name ASC');
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
				$module = 'masterlist';
			break;
			case 'country':
				$template = 'crud_country.twig';
				$args['action'] = 'Create Country';
				$module = 'country';
			break;
			case 'advertisement':
				$template = 'crud_ad.twig';
				$args['action'] = 'Create Ad';
				$module = 'ads';
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
			
			break;
			case 'about-page':
				$template = 'crud_about.twig';
				$args['action'] = 'Create Page';
				$args['scripts'] = [
					'js/plugins/wysihtml5/wysihtml5-0.3.0.min.js',
					'js/plugins/bootstrap3-wysihtml5/bootstrap3-wysihtml5.all.min.js',
					'js/plugins/ckeditor/ckeditor.js',
					'js/plugins/marked/marked.js',
					'js/plugins/to-markdown/to-markdown.js',
					'js/plugins/bootstrap-markdown/bootstrap-markdown.js',
					'js/demo/ui-elements.js',
					'js/about.js'
				];
				$module = 'about';
			break;
			case 'affiliate':
				$module = 'affiliates';
				$template = 'crud_affiliate.twig';
				$args['action'] = 'Create';
				$args['scripts'] = [
					'js/affiliate.js'
				];
			break;
			case 'bulk':
				$module = 'masterlist';
				$template = 'crud_bulk.twig';
				$args['scripts'] = [
					'js/bulk.js'
				];
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
			break;
			case false:
			default:
				$app->pass();
			break;
		}
		if(!$admin->can($module,'create')){
			$app->redirect('/admin/unauthorized');
		}
		parent::render($template,$args);
	}
	
	public function edit()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		$admin = $app->session['admin'];
		$t = isset($get['t']) ? $get['t'] : false;
		$id = isset($get['id']) ? $get['id'] : false;
		if(empty($id)){
			$app->pass();
		}
		switch($t){
			case 'profile':
				$template = 'crud_profile.twig';
				$args['action'] = 'Edit People Profile';
				$args['categories'] = $db->getAll('SELECT * FROM category ORDER BY name ASC');
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
				$args['item'] = $db->getPeopleProfile($id);
				$module = 'masterlist';
			break;
			case 'meme':
				$template = 'crud_meme.twig';
				$args['action'] = 'Edit Meme';
				$args['item'] = $db->getRow('SELECT * FROM masterlist WHERE type_id="2" AND id=:id',[':id'=>$id]);
				$module = 'masterlist';
			break;
			case 'funfact':
				$template = 'crud_funfact.twig';
				$args['action'] = 'Edit Fun Fact';
				$args['categories'] = $db->getAll('SELECT * FROM category ORDER BY name ASC');
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
				$args['item'] = $db->getPeopleProfile($id);
				$module = 'masterlist';
			break;
			case 'country':
				$template = 'crud_country.twig';
				$args['action'] = 'Edit Country';
				$args['item'] = $db->getRow('SELECT * FROM country WHERE id=:id',[':id'=>$id]);
				$module = 'country';
			break;
			case 'advertisement':
				$template = 'crud_ad.twig';
				$args['action'] = 'Edit Ad';
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
				$args['item'] = $db->getRow('SELECT * FROM ad WHERE id=:id',[':id'=>$id]);
				if($args['item']['type'] == 'image'){
					$args['item']['images'] = json_decode($args['item']['images'],true);
				}
				$module = 'ads';
			break;
			case 'banner':
				$template = 'crud_banner.twig';
				$args['action'] = 'Edit Banner';
				$args['item'] = $db->getRow('SELECT * FROM slide WHERE id=:id',[':id'=>$id]);
				$module = 'banners';
			break;
			case 'about-page':
				$template = 'crud_about.twig';
				$args['action'] = 'Edit Page';
				$args['item'] = $db->getRow('SELECT * FROM about WHERE id=:id',[':id'=>$id]);
				$args['scripts'] = [
					'js/plugins/wysihtml5/wysihtml5-0.3.0.min.js',
					'js/plugins/bootstrap3-wysihtml5/bootstrap3-wysihtml5.all.min.js',
					'js/plugins/ckeditor/ckeditor.js',
					'js/plugins/marked/marked.js',
					'js/plugins/to-markdown/to-markdown.js',
					'js/plugins/bootstrap-markdown/bootstrap-markdown.js',
					'js/demo/ui-elements.js',
					'js/about.js'
				];
				$module = 'about';
			break;
			case 'suggestion':
				$template = 'crud_suggestion.twig';
				$args['action'] = 'Edit Suggestion';
				$args['regions'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');
				$args['item'] = $db->getRow('SELECT * FROM suggestion WHERE id=:id',[':id'=>$id]);
				$module = 'suggestions';
			break;
			case 'mail_template':
				$template = 'crud_email.twig';
				$args['action'] = 'Edit Email Template';
				$args['item'] = $db->getRow('SELECT * FROM mailtemplate WHERE id=:id',[':id'=>$id]);
				$args['scripts'] = [
					'js/plugins/wysihtml5/wysihtml5-0.3.0.min.js',
					'js/plugins/bootstrap3-wysihtml5/bootstrap3-wysihtml5.all.min.js',
					'js/plugins/ckeditor/ckeditor.js',
					'js/plugins/marked/marked.js',
					'js/plugins/to-markdown/to-markdown.js',
					'js/plugins/bootstrap-markdown/bootstrap-markdown.js',
					'js/demo/ui-elements.js',
					'js/about.js'
				];
				$module = 'mail';
			break;
			case 'affiliate':
				$template = 'crud_affiliate.twig';
				$args['action'] = 'Edit';
				$args['scripts'] = [
					'js/affiliate.js'
				];
				$args['item'] = \R::getRow('SELECT * FROM admin WHERE id=:id',[':id'=>$id]);
				$module = 'affiliates';
				$params = ['info','permissions'];
			break;
			case false:
			default:
				$app->pass();
			break;
		}
		
		if(isset($params)){
			$allowed = false;
			foreach($params as $param){
				if($admin->can($module,$param)){
					$allowed = true;
				}
			}
			if($allowed == false){
				$app->redirect('/admin/unauthorized');
			}
		}else{
			if(!$admin->can($module,'edit')){
				$app->redirect('/admin/unauthorized');
			}
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
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
		];
		
		$args['countries'] = $db->getAll('SELECT * FROM country ORDER BY name ASC');

		parent::render('countries.twig',$args);
	}
	
	public function about()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		$args['scripts'] = [
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
		];
		
		$args['about'] = $db->getAll('SELECT * FROM about WHERE 1');

		parent::render('about.twig',$args);
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
			
			case 2:
			
			
			break;
			
			case 3:
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
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
		];
		
		if(!$app->session['admin']->can('affiliates','view')){
			$app->redirect('/admin/unauthorized');
		}
		
		$args['affiliates'] = $db->getAll('SELECT * FROM admin WHERE 1');
		parent::render('affiliates.twig',$args);
	}
	
	public function ads()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		$args['scripts'] = [
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
		];
		
		$args['ads'] = $db->getAll('SELECT * FROM ad WHERE 1');
		parent::render('ads.twig',$args);
	}
	
	public function banners()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		$args['scripts'] = [
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
		];
		
		$args['banners'] = $db->getAll('SELECT * FROM slide WHERE 1');
		parent::render('banners.twig',$args);
	}
	
	public function suggestion()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		if(!$app->session['admin']->can('suggestions','view')){
			$app->redirect('/admin/unauthorized');
		}
		
		$args['scripts'] = [
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
		];
		
		$args['banners'] = $db->getAll('SELECT * FROM slide WHERE 1');
		
		if(isset($get['by_email'])){
			if(!empty($get['by_email'])){
				$email = $get['by_email'];
				$args['suggestions'] = $db->getSuggestions($email);
				$args['hide_extra'] = true;
				parent::render('suggestions.twig',$args);
			}else{
				$args['counts'] = $db->getAll('SELECT COUNT(email) AS num,email FROM suggestionstats GROUP BY email');
				parent::render('suggestions_email.twig',$args);
			}
		}else{
			$args['suggestions'] = $db->getSuggestions();
			parent::render('suggestions.twig',$args);
		}
		
		
	}
	
	public function mail_templates()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		$args['scripts'] = [
			'js/plugins/datatables/datatables.min.js',
			'js/masterlist.js'
		];
		
		$args['styles'] = [
			'css/datatable/dataTables.responsive.css',
		];
		
		$args['mail'] = $db->getAll('SELECT * FROM mailtemplate');
		parent::render('mail_templates.twig',$args);
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
	