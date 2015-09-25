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
		
		if(!isset($get['c'])){
			$c = false;
		}else{
			$c = $get['c'];
			$args['c'] = $c;
		}
		
		if(!isset($get['q'])){
			$q = false;
		}else{
			$q = $get['q'];
			$args['q'] = $q;
		}
		
		if(isset($get['t'])){
			$args['t'] = $get['t'];
			switch($get['t']){
				case 'approved':
					$args['queue'] = $db->getAQueue('approved',1,1,$c,$q);
				break;
				case 'pending':
					$args['queue'] = $db->getAQueue('approved',0,1,$c,$q);
				break;
				case 'trash':
					$args['queue'] = $db->getAQueue('deleted',1,1,$c,$q);
				break;
				default:
					$app->redirect('/admin');
				break;
			}
		}else{
			$args['queue'] = $db->getAQueue(false,false,1,$c,$q);
		}
		$args['pending'] = $this->app->db->countPending('queue');
		$args['approved'] = $this->app->db->countApproved('queue');
		$args['form_type'] = isset($get['t']) ? $get['t'] : 'all';
		parent::render('admin_index.twig',$args);
	}
	
	public function artists()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		if(!isset($get['q'])){
			$q = false;
		}else{
			$q = $get['q'];
			$args['q'] = $q;
		}
		
		if(isset($get['t'])){
			$args['t'] = $get['t'];
			switch($get['t']){
				case 'published':
					$args['artists'] = $db->getAArtists('pending',0,1,$q);
				break;
				case 'pending':
					$args['artists'] = $db->getAArtists('pending',1,1,$q);
				break;
				case 'trash':
					$args['artists'] = $db->getAArtists('deleted',1,1,$q);
				break;
				default:
					$app->redirect('/admin/artists');
				break;
			}
		}else{
			$args['artists'] = $db->getAArtists(false,false,1,$q);
		}
		$args['pending'] = $this->app->db->countPending('artists');
		$args['approved'] = $this->app->db->countApproved('artists');
		$args['form_type'] = isset($get['t']) ? $get['t'] : 'all';
		parent::render('admin_artists.twig',$args);
	}
	
	public function comments()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		if(!isset($get['q'])){
			$q = false;
		}else{
			$q = $get['q'];
			$args['q'] = $q;
		}
		
		if(isset($get['t'])){
			$args['t'] = $get['t'];
			switch($get['t']){
				case 'seen':
					$args['comments'] = $db->getAComments('seen',1,1,$q);
				break;
				case 'new':
					$args['comments'] = $db->getAComments('seen',0,1,$q);
				break;
				case 'flagged':
					$args['comments'] = $db->getAComments('flagged',1,1,$q);
				break;
				default:
					$app->redirect('/admin/comments');
				break;
			}
		}else{
			$args['comments'] = $db->getAComments(false,false,1,$q);
		}
		$args['pending'] = $this->app->db->countPending('comments');
		$args['approved'] = $this->app->db->countApproved('comments');
		$args['form_type'] = isset($get['t']) ? $get['t'] : 'all';
		parent::render('admin_comments.twig',$args);
	}
	
	public function users()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		
		if(!isset($get['q'])){
			$q = false;
		}else{
			$q = $get['q'];
			$args['q'] = $q;
		}
		
		if(isset($get['t'])){
			$args['t'] = $get['t'];
			switch($get['t']){
				case 'banned':
					$args['users'] = $db->getAUsers('banned',1,1,$q);
				break;
				case 'trash':
					$args['users'] = $db->getAUsers('deleted',1,1,$q);
				break;
				default:
					$app->redirect('/admin/users');
				break;
			}
		}else{
			$args['users'] = $db->getAUsers(false,false,1,$q);
		}
		$args['form_type'] = isset($get['t']) ? $get['t'] : 'all';
		parent::render('admin_users.twig',$args);
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
	
	public function edit_track()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		if(!isset($get['id'])){
			die('artist id missing');
		}
		$args['track'] = $db->getTrackById($get['id']);
		parent::render('admin_edit_track.twig',$args);
	}
	
	public function edit_mixtape()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		if(!isset($get['id'])){
			die('artist id missing');
		}
		$args['mixtape'] = $db->getMixtapeById($get['id']);
		parent::render('admin_edit_mixtape.twig',$args);
	}
	
	public function edit_artist()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		if(!isset($get['id'])){
			die('artist id missing');
		}
		$args['artist'] = $db->getArtistById($get['id']);
		parent::render('admin_edit_artist.twig',$args);
	}
	
	public function banners()
	{
		$app  = $this->app;
		$args = $app->args;
		$get  = $app->get;
		$db   = $app->db;
		$args['banners'] = $db->getBanners();
		parent::render('admin_banners.twig',$args);
	}
	
}
	