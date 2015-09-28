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
		$ip = $app->remote_addr;
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
					if(count($tmp) !== 16){
						throw new \exception('number of elements does not match specification.');
					}
					$all = range(0,15);
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

} 