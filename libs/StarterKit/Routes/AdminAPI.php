<?php

namespace StarterKit\Routes;

class AdminAPI
{
	use \StarterKit\Traits\Upload;
	public $app; //app is available so you can call any method/property on app from within here.
	function __construct()
	{
		$this->app = \StarterKit\App::getInstance();
	}
	
	public function __call($method,$args = null)
	{
		throw new \exception('method `'.$method.'` doesnt exist');
	}
	
	public function __try($method)
	{
		if(!$this->app->is_admin() && $method !== 'auth'){
			throw new \exception('denied');
		}
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
	
	public function auth()
	{
		$app = $this->app;
		if($app->is_admin()){
			throw new \exception('You are already logged in');
		}
		$post = $app->post;
		$required = [
			'email',
			'password'
		];
		foreach($required as $k){
			if(!isset($post[$k])){
				throw new \exception('Missing form value :'.ucFirst($k));
			}	
		}
		$user = new \StarterKit\Admin($post['email'],$post['password'],true);	
		return ['error'=>0,'message'=>0];
	}
	
	public function keepalive()
	{
		$this->session['admin']->keepalive();
		return ['error'=>0,'message'=>1];
	}

	public function update_setting()
	{
		$app = $this->app;
		$get = $app->get;
		
		if(!isset($get['x'])){
			throw new \exception('missing required operator');
		}
		
		switch($get['x']){
			case 'sidebar':
				$v = isset($get['v']) ?  (int) $get['v'] : 0;
				if(!in_array($v,[0,1])){
					throw new \exception('invalid val');
				}
				$app->session['admin']->sidebar = $v;
				$app->session['admin']->update();
			break;
			case 'menu':
				$v = isset($get['v']) ? $get['v'] : '';
				if(!empty($v)){
					//run diagnostic to ensure proper results.
					if(strpos($v,',') === false){
						throw new \exception('formatting error');
					}
					$tmp = array_map('intval',explode(',',$v));
					if(count($tmp) !== 15){
						throw new \exception('number of elements does not match specification.');
					}
					$all = range(0,14);
					if(sort($all) !== sort($tmp)){
						throw new \exception(json_encode([$all,$tmp]));
					}
				}
				$app->session['admin']->order = $v;
				$app->session['admin']->update();
			break;
		}

		return ['error'=>0,'message'=>1];
	}
	
	public function user_settings()
	{
		$app = $this->app;
		$filter = $app->filter;
		$post = $app->post; 
		$db = $app->db;
		$admin = $app->session['admin'];
		$id = $admin->id;
		
		$filter->custom_filter('custom_name',function($input) use($filter,$db,$id){
			$input = trim($input);
			$min = 4;
			$max = 55;
			if( !preg_match( '/^[A-Za-z.\-_0-9 ]{'.$min.','.$max.'}$/', $input ) ){
				throw new \exception('Username may only contain letters, numbers, periods, spaces, and underscores.');
			}
			if(!empty($db->getRow('SELECT id FROM admin WHERE id!=:id AND name=:name',[':id'=>$id,':name'=>$input]))){
				throw new \exception('This username is already taken.');
			}
			return $input;
		});
		
		$filter->custom_filter('custom_email',function($input) use($filter,$db,$id){
			$input = $filter->email($input);
			if(!empty($db->getRow('SELECT id FROM admin WHERE id!=:id AND email=:email',[':id'=>$id,':email'=>$input]))){
				throw new \exception('That Email address is already in use');
			}
			return $input;
		});
		
		$admin->name = $filter->custom_name($post['name']);
		$admin->email = $filter->custom_email($post['email']);
		
		try
		{
			$img = $admin->avatar;
			$upl = trim(implode('',$this->square_thumbs('avatar_img',$app->files,[120],120,120)));
			
			if(!empty($img) && $admin->avatar !== $img && $upl !== null){
				$this->delFile($img);
				$admin->avatar = $upl;
			}
		}
		catch(\exception $e)
		{
			if(!empty($app->files) && isset($app->files['avatar_img']) && is_uploaded_file($app->files['avatar_img']['tmp_name'])){
				throw $e;
			}
		}
		
		if(isset($post['currpw']) && !empty($post['currpw'])){
			if(!$admin->verify($post['currpw'])){
				throw new \exception('Invalid Existing password');
			}
			if(isset($post['newpass']) && isset($post['confirmpass'])){
				if(!empty($post['newpass']) && !empty($post['confirmpass'])){
					if($post['newpass'] !== $post['confirmpass']){
						throw new \exception('Password Missmatch');
					}else{
						$admin->password = $filter->password_hash($post['newpass']);
					}
				}else{
					throw new \exception('New Password and Confirm Password are required to change your password.');
				}
			}
		}
		
		$admin->update();
		
		return ['error'=>0,'message'=>1];
	}

} 