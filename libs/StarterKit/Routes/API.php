<?php

namespace StarterKit\Routes;

class API
{
	use \StarterKit\Traits\Upload;
	public $app; //app is available so you can call any method/property on app from within here.
	function __construct()
	{
		$this->app = $app = \StarterKit\App::getInstance();
		if(!$app->debug){
			if($app->slim->request->getPath() !== '/api/init'){
				if($app->slim->request->isPost()){
					$fail = false;
					if(!isset($app->post['c'])){
						$fail = true;
					}else{
						if(urldecode($app->post['c']) !== $app->session['csrf']){
							$fail = true;
						}
					}
					if($fail !== false){
						$app->halt(403,'Access Denied');
					}
				}

				if($app->slim->request->isGet()){
					$fail = false;
					if(!isset($app->get['c'])){
						$fail = true;
					}else{
						if(urldecode($app->get['c']) !== $app->session['csrf']){
							$fail = true;
						}
					}
					if($fail !== false){
						$app->halt(403,'Access Denied');
					}
				}	
			}
		}	
	}
		
	
	public function __call($method,$args = null)
	{
		throw new \exception('method `'.$method.'` doesnt exist');
	}
	
	public function __try($method)
	{
		try{
			if(!method_exists($this,$method)){
				throw new \exception('method not found');
			}else{
				$reflection = new \ReflectionMethod($this, $method);
				if (!$reflection->isPublic()) {
					throw new \exception('method not found');
				}
			}
			$msg = call_user_func([$this,$method]);
		}
		catch(\exception $e){
			if($this->app->debug === true){
				$err = $e->getMessage().'<br/><hr>'.$e->getFile().' @ Line '.$e->getLine() .'<br/><hr>STACK TRACE:<br/>'.$e->getTraceAsString();
			}else{
				$err = $e->getMessage();
			}
			$msg = ['error'=>1,'message'=>$err];
		}
		echo json_encode($msg, JSON_HEX_QUOT | JSON_HEX_TAG);
	}
	
	public function init()
	{
		return ['error'=>0,'message'=>$this->app->session['csrf']];
	}
	
	public function is_user_logged_in()
	{
		return ['error'=>0,'message'=>intval($this->app->is_user())];
	}
	
	public function auth_user()
	{
		$app = $this->app;
		if($app->is_user()){
			throw new \exception('You are already logged in');
		}
		$post = $app->post;
		$ip = $app->remote_addr;
		$required = [
			'email',
			'password'
		];
		foreach($required as $k){
			if(!isset($post[$k]))
				throw new \exception('You must provide a '.ucFirst($k));
		}
		
		$user = new \StarterKit\User($post['email'],$post['password'],true);	
		
		return ['error'=>0,'message'=>0];
	}
	
	public function logout()
	{
		if($this->app->is_user()){
			$this->app->session['user']->logout();
		}
		return ['error'=>0,'message'=>0];
	}
	
	public function create_user()
	{
		$app = $this->app;
		$filter = $app->filter;
		$post = $app->post; 
		$db = $app->db;
		if($app->is_user()){
			throw new \exception('You may not create an account while already logged in.');
		}

		$required = [
			'email'=>'custom_email',
			'password'=>'password_hash',
			'user_name'=>'custom_name',
		];
		
		$filter->custom_filter('custom_name',function($input) use($filter,$db){
			$input = trim($input);
			$min = 4;
			$max = 55;
			if( !preg_match( '/^[A-Za-z.\-_0-9 ]{'.$min.','.$max.'}$/', $input ) ){
				throw new \exception('Username may only contain letters, numbers, periods, spaces, and underscores.');
			}
			if($db->exists('user','user_name',$input)){
				throw new \exception('This username is already taken.');
			}
			return $input;
		});
		
		$filter->custom_filter('custom_email',function($input) use($filter,$db){
			$input = $filter->email($input);
			if($db->exists('user','email',$input)){
				throw new \exception('That Email address is already in use');
			}
			return $input;
		});
		
		/*
		$accept_tos = (int) isset($post['tos_accept']) && $post['tos_accept'] == 1; // 1 or 0
		if($accept_tos != 1){
			throw new \exception('You must accept the agreement in order to register an account.');
		}
		*/
		$accept_tos = 1;
		$user = $db->model('user');
		$filter->generate_model($user,$required,[],$post);
		$user->registered = time();
		$user->remote_addr = $app->remote_addr;
		$user->accept_tos = $accept_tos;
		$user->deleted = 0;
		$user->banned  = 0;
		
		$id = $db->store($user);
		$user2 = new \StarterKit\User($user->email,$post['password'],true); //third parameter is rememberme
		return ['error'=>0,'message'=>1];
	}
	
	public function keepalive()
	{
		if($this->app->is_user()){
			$this->app->session['user']->keepalive();
			return ['error'=>0,'message'=>0];
		}
		throw new \exception('');
	}
	
	public function recover_account()
	{
		$app = $this->app;
		$filter = $app->filter;
		$db = $app->db;
		$smtp = $app->smtp;
		if($app->is_user()){
			throw new \exception('You are already logged in');
		}
		$post = $app->post;
		$required = [
			'email'
		];
		foreach($required as $k){
			if(!isset($post[$k])){
				throw new \exception('Missing Required Parameter');
			}
		}
		$email = $filter->email($post['email']);
		
		if(!$db->exists('user','email',$email)){
			throw new \exception('Email not found.');
		}
		
		$t = $db->model('recover');
		$t->remote_addr = $app->remote_addr;
		$t->email       = $email;
		$t->token       = $token = hash_hmac('sha256',md5($email . time()).md5(uniqid("",true)), md5($app->remote_addr));
		$db->store($t);
		
		$args = [
			'sitename'=>$app->args['sitename'],
			'send_from'=>$app->smtp_config['send_from'],
			'subject'=>'Reset Password',
			'ip'=>$app->remote_addr,
			'recipient'=>$email,
			'recover_url'=>$app->args['base_url'].'reset_password?action=CONFIRM&token='.$token,
			'cancel_url'=>$app->args['base_url'].'reset_password?action=CANCEL&token='.$token
		];
		$html = $smtp->create_html('reset_password.twig',$args);
		$smtp->send($html,'Reset Password',$email,false);
		return ['error'=>0,'message'=>'An email with instructions detailing how to reset your password was sent to '.$email.'.'];
	}
	
	public function reset_password()
	{
		$app = $this->app;
		$db  = $app->db;
		$post = $app->post;
		$filter = $app->filter;
		if(!isset($post['token'])){
			throw new \exception('Missing token');
		}
		$details = $db->fetchRecoverDetails($post['token']);
		if(empty($details)){
			throw new \exception('Invalid token');
		}
		if(!isset($post['pass'])){
			throw new \exception('Missing password');
		}
		if(!isset($post['cpass'])){
			throw new \exception('You must confirm your password');
		}
		if($post['pass'] !== $post['cpass']){
			throw new \exception('Password Mismatch');
		}
		$hash = $filter->password_hash($post['pass']);
		$db->updateUserPassword($hash,$details['email']);
		$user = new \StarterKit\User($details['email'],false);
		$db->trash('recover',$details['id']);
		return ['error'=>0,'message'=>1];
	}
	
	public function change_password()
	{
		$app = $this->app;
		$db  = $app->db;
		$args = $app->args;
		$post = $app->post;
		if(!$app->is_user()){
			throw new \exception('You must be logged in to change password.');
		}
		$required = [
			'new_password',
			'current_password'
		];
		foreach($required as $k){
			if(!isset($post[$k])){
				throw new \exception('New password and Current Password are required.');
			}
			if(empty($post[$k])){
				throw new \exception('New password and Current Password are required.');
			}
			if(strlen($post[$k]) < 6){
				throw new \exception('Passwords must be atleast 6 charachters in length');
			}
		}
		$app->session['user']->update_password($post['current_password'],$post['new_password']);
		return ['error'=>0,'message'=>1];
	}
	
	public function getframeskeleton()
	{
		//returns the api slug skeleton which our app needs
		return ['error'=>0,'message'=>$this->app->db->slugs()];
	}
}