<?php

namespace StarterKit\Routes;

class User extends ViewController
{
	public $app;
	function __construct()
	{
		$this->app = (\StarterKit\App::getInstance());
		parent::__construct();
		if(!$this->app->is_user()){
			$this->app->redirect('/create_account');
		}
	}
	
	public function profile()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$db = $app->db;
		$args['singles'] = $db->getUserFavoritesFull('singles',$args['user']);
		$args['mixtapes'] = $db->getUserFavoritesFull('mixtapes',$args['user']);
		$args['scripts'] = ['tbl.js','paginater.js'];
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/user_profile.twig',$args);
		}else{
			parent::render('user_profile.twig',$args);
		}
	}
	
	public function change_password()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Change Password';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/user_password.twig',$args);
		}else{
			parent::render('user_password.twig',$args);
		}
	}
	
	public function change_avatar()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Change Avatar';
		$args['scripts'] = ['plugin/cropper/cropper.min.js','avatar.js'];
		$args['styles']  = 'plugin/cropper/cropper.min.css';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/user_avatar.twig',$args);
		}else{
			parent::render('user_avatar.twig',$args);
		}
	}
	
	public function upload()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Upload';
		$args['scripts'] = ['plugin/id3/id3.min.js','plugin/md5.min.js','upload.2.js'];
		$args['scripts_external'] = '//code.jquery.com/ui/1.11.4/jquery-ui.js';
		$args['styles_external']  = '//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css';
		parent::render('user_upload.2.twig',$args);
	}
	
	public function logout()
	{
		if($this->app->is_user()){
			$this->app->session['user']->logout();
		}
		$this->app->redirect('/');
	}
	
}