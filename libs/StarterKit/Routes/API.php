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
	
	public function auth_admin()
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
	
	public function change_avatar()
	{
		$app = $this->app;
		$db  = $app->db;
		$args = $app->args;
		if(!$app->is_user()){
			throw new \exception('You must be logged in to change profile pictures.');
		}
		list($small,$large) = $this->square_thumbs('userfile',$app->files,[32,300],100,100);
		$app->session['user']->update_avatar($small,$large);
		return ['error'=>0,'message'=>$large];
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
	
	public function comment()
	{
		$app = $this->app;
		$db  = $app->db;
		$post = $app->post;
		$filter = $app->filter;
		$args = $app->args;
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to comment.');
		}
		if(!isset($post['type'])){
			throw new \exception('');
		}
		switch($post['type']){
			case 'mixtape':
				$required = [
					'mixtape_id'=>'mixtape_exists',
					'comment'=>['min','not_empty']
				];
			break;
			case 'single':
				$required = [
					'track_id'=>'single_exists',
					'comment'=>['min','not_empty']
				];
			break;
			default:
				throw new \exception('Invalid type specified');
			break;
		}
		$user = $app->session['user'];
		if($user->is_banned()){
			throw new \exception('You have been banned from commenting on this website.');
		}else{
			if($user->is_spamming()){
				throw new \exception('You have been commenting at an abnormal rate. your account has been flagged for review.');
			}
		}
		$t = $db->model('comment');
		$t->user_id = $user->id;
		$t->timestamp = time();
		$t->seen = 0;
		$t->flagged = 0;
		
		$filter->custom_filter('single_exists',function($input) use($db){
			if(!$db->exists('track','id',$input)){
				throw new \exception('Invalid resource');
			}
			return $input;
		});
		
		$filter->custom_filter('mixtape_exists',function($input) use($db){
			if(!$db->exists('mixtape','id',$input)){
				throw new \exception('Invalid resource');
			}
			return $input;
		});
		
		$filter->custom_filter('not_empty',function($input){
			if(empty($input)){
				throw new \exception('Comment cant be blank.');
			}
			return $input;
		});
		
		$filter->generate_model($t,$required,[],$post);
		
		$id = $db->store($t);
		
		$args['comments'] = [
			[
				'user_avatar'=>$user->small,
				'comment'=>$t->comment,
			]
		];
		return ['error'=>0,'message'=>$this->getTwig()->render('partials/comment.twig',$args)];
	}
	
	public function like()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		$filter = $app->filter;
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to like content.');
		}
		$user = $app->session['user'];
		if(!isset($get['type'])){
			throw new \exception('');
		}
		switch($get['type']){
			case 'mixtape':
				$required = [
					'mixtape_id'=>'mixtape_exists',
				];
				$ftype = 'mixtape_id';
			break;
			case 'single':
				$required = [
					'track_id'=>'single_exists',
				];
				$ftype = 'track_id';
			break;
			default:
				throw new \exception('Invalid type specified');
			break;
		}
		
		$filter->custom_filter('single_exists',function($input) use($db,$user){
			if(!$db->exists('track','id',$input)){
				throw new \exception('Invalid resource');
			}
			if($db->userHasLiked('single',$input,$user->id)){
				throw new \exception('Already favorited');
			}
			return $input;
		});
		
		$filter->custom_filter('mixtape_exists',function($input) use($db,$user){
			if(!$db->exists('mixtape','id',$input)){
				throw new \exception('Invalid resource');
			}
			if($db->userHasLiked('mixtape',$input,$user->id)){
				throw new \exception('Already favorited');
			}
			return $input;
		});
		$t = $db->model('likes');
		$t->user_id = $user->id;
		$t->timestamp = time();
		$filter->generate_model($t,$required,[],$get);
		$db->store($t);
		$user->addlike($get['type'],$t->{$ftype});
		return ['error'=>0,'message'=>1];
	}
	
	public function favorite()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		$filter = $app->filter;
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to favorite content.');
		}
		$user = $app->session['user'];
		if(!isset($get['type'])){
			throw new \exception('');
		}
		switch($get['type']){
			case 'mixtape':
				$required = [
					'mixtape_id'=>'mixtape_exists',
				];
				$ftype = 'mixtape_id';
			break;
			case 'single':
				$required = [
					'track_id'=>'single_exists',
				];
				$ftype = 'track_id';
			break;
			default:
				throw new \exception('Invalid type specified');
			break;
		}
		
		$filter->custom_filter('single_exists',function($input) use($db,$user){
			if(!$db->exists('track','id',$input)){
				throw new \exception('Invalid resource');
			}
			if($db->userHasFavorited('single',$input,$user->id)){
				throw new \exception('Already favorited');
			}
			return $input;
		});
		
		$filter->custom_filter('mixtape_exists',function($input) use($db,$user){
			if(!$db->exists('mixtape','id',$input)){
				throw new \exception('Invalid resource');
			}
			if($db->userHasFavorited('mixtape',$input,$user->id)){
				throw new \exception('Already favorited');
			}
			return $input;
		});
		$t = $db->model('favorites');
		$t->user_id = $user->id;
		$t->timestamp = time();
		$filter->generate_model($t,$required,[],$get);
		$db->store($t);
		$user->addfave($get['type'],$t->{$ftype});
		return ['error'=>0,'message'=>1];
	}
	
	public function remove_like()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to remove a like.');
		}
		$user = $app->session['user'];
		if(!isset($get['type']) || !isset($get['id'])){
			throw new \exception('');
		}
		switch($get['type']){
			case 'mixtape':
				$db->removeLike('mixtape',$get['id'],$user->id);
				$user->rmlike('mixtape',$get['id']);
			break;
			case 'single':
				$db->removeLike('single',$get['id'],$user->id);
				$user->rmlike('single',$get['id']);
			break;
			default:
				throw new \exception('Invalid type specified');
			break;
		}
		return ['error'=>0,'message'=>1];
	}
	
	public function remove_favorite()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to remove a favorite.');
		}
		$user = $app->session['user'];
		if(!isset($get['type']) || !isset($get['id'])){
			throw new \exception('');
		}
		switch($get['type']){
			case 'mixtape':
				$db->removeFavorite('mixtape',$get['id'],$user->id);
				$user->rmfave('mixtape',$get['id']);
			break;
			case 'single':
				$db->removeFavorite('single',$get['id'],$user->id);
				$user->rmfave('single',$get['id']);
			break;
			default:
				throw new \exception('Invalid type specified');
			break;
		}
		return ['error'=>0,'message'=>1];
	}
	
	public function view()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		
		if(!isset($get['type']) || !isset($get['id'])){
			throw new \exception('');
		}
		
		$type = $get['type'];
		$id   = $get['id'];
		if(empty($id) || empty($type)){
			throw new \exception('');
		}
		$uniq = $app->is_user() ? md5($app->session['user']->user_name) : md5('guest');
		$db->viewIncr($type,$id,$uniq);
		return ['error'=>0,'message'=>1];
	}
	
	
	public function paginate()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		$args = $app->args;
		
		if(!isset($get['type'])){
			throw new \exception('missing type');
		}
		if(!isset($get['page'])){
			throw new \exception('missing page');
		}
		if(!isset($get['sort'])){
			$sort = false;
		}else{
			$sort = $get['sort'];
		}
		if(!isset($get['timespan'])){
			$timespan = false;
		}else{
			$timespan = $get['timespan'];
		}
		if(!isset($get['q'])){
			$q = false;
		}else{
			$q = $get['q'];
		}
		
		if(!isset($get['artist'])){
			$artist = false;
		}else{
			$artist = $get['artist'];
		}
		
		if(!isset($get['media_type'])){
			$media_type = false;
		}else{
			$media_type = $get['media_type'];
		}
		
		if(!isset($get['mobile'])){
			$mobile = false;
		}else{
			$mobile = $get['mobile'];
		}
		
		switch($get['type']){
			case 'singles':
				$args['singles'] = $db->getSingles($get['page'],$sort,$timespan);
				if($mobile){
					$template = 'mobile/partials/single_row.twig';
				}else{
					$template = 'partials/single_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['singles'])]];
			break;
			case 'mixtapes':
				$args['mixtapes'] = $db->getMixtapes($get['page'],$sort,$timespan);
				if($mobile){
					$template = 'mobile/partials/mixtape_row.twig';
				}else{
					$template = 'partials/mixtape_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['mixtapes'])]];
			break;
			case 'user-singles':
				if(!$app->is_user()){
					throw new \exception('must be logged in to use this method');
				}
				$args['singles'] = $db->getUserFavoritesFull('singles',$args['user'],$get['page']);
				if($mobile){
					$template = 'mobile/partials/single_row.twig';
				}else{
					$template = 'partials/single_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['singles'])]];
			break;
			case 'user-mixtapes':
				if(!$app->is_user()){
					throw new \exception('must be logged in to use this method');
				}
				$args['mixtapes'] = $db->getUserFavoritesFull('mixtapes',$args['user'],$get['page']);
				if($mobile){
					$template = 'mobile/partials/mixtape_row.twig';
				}else{
					$template = 'partials/mixtape_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['mixtapes'])]];
			break;
			case 'search-singles':
				if($q == false){
					throw new \exception('missing paramter `q`');
				}
				$args['singles'] = $db->searchPaginate($q,'singles',$get['page']);
				if($mobile){
					$template = 'mobile/partials/single_row.twig';
				}else{
					$template = 'partials/single_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['singles'])]];
			break;
			case 'search-mixtapes':
				if($q == false){
					throw new \exception('missing paramter `q`');
				}
				$args['mixtapes'] = $db->searchPaginate($q,'mixtapes',$get['page']);
				if($mobile){
					$template = 'mobile/partials/mixtape_row.twig';
				}else{
					$template = 'partials/mixtape_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['mixtapes'])]];
			break;
			case 'artists':
				$args['artists'] = $app->db->cachedCall('getArtists',[$get['page']],60,false,false);
				$html = $this->getTwig()->loadTemplate('partials/artists_row.twig')->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['artists'])]];
			break;
			case 'artist-singles':
				if($artist == false){
					throw new \exception('missing paramter `artist`');
				}
				$args['singles'] = $db->artistContent($artist,'single',$get['page']);
				if($mobile){
					$template = 'mobile/partials/single_row.twig';
				}else{
					$template = 'partials/single_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['singles'])]];
			break;
			case 'artist-mixtapes':
				if($artist == false){
					throw new \exception('missing paramter `artist`');
				}
				$args['mixtapes'] = $db->artistContent($artist,'mixtape',$get['page']);
				if($mobile){
					$template = 'mobile/partials/mixtape_row.twig';
				}else{
					$template = 'partials/mixtape_row.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['mixtapes'])]];
			break;
			case 'comments':
				if($media_type == false){
					throw new \exception('missing paramter `media_type`');
				}
				$args['comments']=$db->getComments($media_type,$get['id'],$get['page']);
				$html = $this->getTwig()->render('partials/comment.twig',$args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['comments'])]];
			break;
			case 'featured-singles':
				$args['featured']= ['singles' => $db->getFeatured('singles',$get['page'])];
				if($mobile){
					$template = 'mobile/partials/featured_singles.twig';
				}else{
					$template = 'partials/featured_singles.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['featured']['singles'])]];
			break;
			case 'featured-mixtapes':
				$args['featured'] = ['mixtapes' => $db->getFeatured('mixtapes',$get['page'])];
				if($mobile){
					$template = 'mobile/partials/featured_singles.twig';
				}else{
					$template = 'partials/featured_mixtapes.twig';
				}
				$html = $this->getTwig()->loadTemplate($template)->render($args);
				return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args['featured']['mixtapes'])]];
			break;
			default:
				throw new \exception('unknown pagination type');
			break;
		}
	}
	
	//creating tracks and mixtapes.
	public function create_single()
	{
		$app = $this->app;
		$db  = $app->db;
		$post = $app->post;
		$filter = $app->filter;
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to create tracks.');
		}
		
		$required_files = [
			'audio_file',
			'image_file'
		];
		
		$required = [
			'title'=>['min','rmnl'],
			'timestamp'=>'cast_int'
		];
		
		$optional = [
			'description'=>'min',
			'youtube'=>'c_youtube',
			'purchase'=>'c_url',
			'featuring'=>['min','rmnl']
		];
		
		$checkboxes = [
			'download',
			'in_app'
		];
		$db->begin_tx();
		try{
			$t = $db->model('track');
			$files_uploaded = [];
			foreach($required_files as $name){
				if(!isset($app->files[$name])){
					$type = ($name == 'audio_file') ? 'Mp3 Audio File' : 'Album Cover Image File';
					throw new \exception('You must select an '.$type);
				}
				if($app->files[$name]['error'])
				{
					throw new \exception($this->upload_errors($app->files[$name]['error']));
				}
				if( !is_uploaded_file($app->files[$name]['tmp_name']) ){
					throw new \exception('Bad file input');
				}
			}
			
			list($t->small,$t->medium,$t->large) = $this->square_thumbs('image_file',$app->files,[60,220,800],240,240);
			
			$files_uploaded[] = [
				'type'=>'image',
				'name'=>$t->small
			];
			$files_uploaded[] = [
				'type'=>'image',
				'name'=>$t->medium
			];
			$files_uploaded[] = [
				'type'=>'image',
				'name'=>$t->large
			];
			$t->audio_file = $this->mp3_upload('audio_file',$app->files);
			$files_uploaded[] = [
				'type'=>'audio',
				'name'=>$t->audio_file
			];
			if(!isset($post['artist'])){
				throw new \exception('Artist cant be left blank');
			}
			if(empty($post['artist'])){
				throw new \exception('Artist cant be left blank');
			}
			
			$artist = $filter->rmnl($filter->min($post['artist']));
			if($db->exists('artist','name',$artist)){
				$t->artist_id = $db->idBy('artist','name',$artist);
				$t2 = $db->model('artist',$t->artist_id);
			}else{
				$t2 = $db->model('artist');
				$t2->name = $artist;
				$t2->uri  = $this->url_safe($artist);
				$t2->pending = 1;
				$t2->small = '';
				$t2->large = '';
				$t2->description = '';
				$t2->facebook = '';
				$t2->twitter = '';
				$t2->youtube = '';
				$t->artist_id = $db->store($t2);
			}
			
			$filter->custom_filter('c_youtube',function($input){
				$input = preg_replace('~
					https?://         # Required scheme. Either http or https.
					(?:[0-9A-Z-]+\.)? # Optional subdomain.
					(?:               # Group host alternatives.
					  youtu\.be/      # Either youtu.be,
					| youtube         # or youtube.com or
					  (?:-nocookie)?  # youtube-nocookie.com
					  \.com           # followed by
					  \S*             # Allow anything up to VIDEO_ID,
					  [^\w\s-]       # but char before ID is non-ID char.
					)                 # End host alternatives.
					([\w-]{11})      # $1: VIDEO_ID is exactly 11 chars.
					(?=[^\w-]|$)     # Assert next char is non-ID or EOS.
					(?!               # Assert URL is not pre-linked.
					  [?=&+%\w.-]*    # Allow URL (query) remainder.
					  (?:             # Group pre-linked alternatives.
						[\'"][^<>]*>  # Either inside a start tag,
					  | </a>          # or inside <a> element text contents.
					  )               # End recognized pre-linked alts.
					)                 # End negative lookahead assertion.
					[?=&+%\w.-]*        # Consume any URL (query) remainder.
					~ix', 
					'$1',
				$input);
				return $input;
			});
			
			$filter->custom_filter('c_url',function($input){
				if(!filter_var($input, FILTER_VALIDATE_URL)){
					throw new \exception('Invalid Purchase URL specified.');
				}
				return $input;
			});
			
			$t->user_id = $app->session['user']->id; //user who uploaded track
			
			$filter->generate_model($t,$required,$optional,$post);
			
			$t->uri = strtolower($t2->uri .'-'.$this->url_safe($t->title));
			
			if(!empty($t->featuring)){
				$ft = explode(',',$t->featuring);
				foreach($ft as $artist){
					$artist = trim($filter->rmnl($filter->min($artist)));
					if(!$db->exists('artist','name',$artist)){
						$t2 = $db->model('artist');
						$t2->name = $artist;
						$t2->uri = $this->url_safe($artist);
						$t2->pending = 1;
						$t2->small = '';
						$t2->large = '';
						$t2->description = '';
						$t2->facebook = '';
						$t2->twitter = '';
						$t2->youtube = '';
						$db->store($t2);
					}
				}
			}
			
			foreach($optional as $k=>$v)
			{
				if(!isset($t->{$k})){
					$t->{$k} = '';
				}
			}

			foreach($checkboxes as $k){
				$t->{$k} = (int) isset($post[$k]) && $post[$k] == 1;
			}
			
			$t->is_single    = 1; // true
			$t->is_mixtape   = 0; // false
			$t->approved    = 0;
			$t->deleted      = 0;
			$t->added = time();
			$id = $db->store($t);
			
			$t3 = $db->model('views');
			$t3->track_id = $id;
			$t3->uniq = 'system_auto';
			$t3->timestamp = time();
			$db->store($t3);
			
			$t4 = $db->model('likes');
			$t4->track_id = $id;
			$t4->user_id  = 0;
			$t4->timestamp = time();
			$db->store($t4);
			$db->commit_tx();
			
		}
		catch(\exception $e){
			$db->rollback_tx();
			foreach($files_uploaded as &$file)
			{
				$file['name'] = array_pop(explode('/',$file['name']));
				$this->s3DeleteFile($file['type'],$file['name']);
			}
			throw $e;
		}
		return ['error'=>0,'message'=>1];
	}
	
	public function init_mixtape()
	{
		$app = $this->app;
		$db  = $app->db;
		$post = $app->post;
		$filter = $app->filter;
		
		
		$required = [
			'title'=>['min','rmnl'],
			'timestamp'=>'cast_int'
		];
		
		$optional = [
			'description'=>'min',
			'youtube'=>'c_youtube',
			'purchase'=>'c_url',
			'featuring'=>['min','rmnl']
		];
		
		$checkboxes = [
			'download',
			'in_app'
		];
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to create tracks.');
		}
		
		if(!isset($post['artist'])){
			throw new \exception('Artist cant be left blank');
		}
		if(empty($post['artist'])){
			throw new \exception('Artist cant be left blank');
		}
		
		if(!isset($app->files['image_file'])){
			throw new \exception('You must select an Album Cover Image File');	
		}
		if($app->files['image_file']['error'])
		{
			throw new \exception($this->upload_errors($app->files['image_file']['error']));
		}
		if( !is_uploaded_file($app->files['image_file']['tmp_name']) ){
			throw new \exception('Bad file input');
		}
		$db->begin_tx();
		try{
			$mixtape = $db->model('mixtape');
			
			$files_uploaded = [];
			
			$artist = $filter->rmnl($filter->min($post['artist']));
			if($db->exists('artist','name',$artist)){
				$mixtape->artist_id = $db->idBy('artist','name',$artist);
				$t2 = $db->model('artist',$mixtape->artist_id);
			}else{
				$t2 = $db->model('artist');
				$t2->name = $artist;
				$t2->uri = $this->url_safe($artist);
				$t2->pending = 1;
				$t2->small = '';
				$t2->large = '';
				$t2->description = '';
				$t2->facebook = '';
				$t2->twitter = '';
				$t2->youtube = '';
				$mixtape->artist_id = $db->store($t2);
			}
			
			foreach($checkboxes as $k){
				$mixtape->{$k} = (int) isset($post[$k]) && $post[$k] == 1;
			}
			
			$filter->custom_filter('c_youtube',function($input){
				$input = preg_replace('~
					https?://         # Required scheme. Either http or https.
					(?:[0-9A-Z-]+\.)? # Optional subdomain.
					(?:               # Group host alternatives.
					  youtu\.be/      # Either youtu.be,
					| youtube         # or youtube.com or
					  (?:-nocookie)?  # youtube-nocookie.com
					  \.com           # followed by
					  \S*             # Allow anything up to VIDEO_ID,
					  [^\w\s-]       # but char before ID is non-ID char.
					)                 # End host alternatives.
					([\w-]{11})      # $1: VIDEO_ID is exactly 11 chars.
					(?=[^\w-]|$)     # Assert next char is non-ID or EOS.
					(?!               # Assert URL is not pre-linked.
					  [?=&+%\w.-]*    # Allow URL (query) remainder.
					  (?:             # Group pre-linked alternatives.
						[\'"][^<>]*>  # Either inside a start tag,
					  | </a>          # or inside <a> element text contents.
					  )               # End recognized pre-linked alts.
					)                 # End negative lookahead assertion.
					[?=&+%\w.-]*        # Consume any URL (query) remainder.
					~ix', 
					'$1',
				$input);
				return $input;
			});
			
			$filter->custom_filter('c_url',function($input){
				if(!filter_var($input, FILTER_VALIDATE_URL)){
					throw new \exception('Invalid Purchase URL specified.');
				}
				return $input;
			});
			
			$filter->generate_model($mixtape,$required,$optional,$post);
			
			$mixtape->uri = strtolower($t2->uri .'-'.$this->url_safe($mixtape->title));
			
			if(!empty($mixtape->featuring)){
				$ft = explode(',',$mixtape->featuring);
				foreach($ft as $artist){
					$artist = trim($filter->rmnl($filter->min($artist)));
					if(!$db->exists('artist','name',$artist)){
						$t2 = $db->model('artist');
						$t2->name = $artist;
						$t2->uri = $this->url_safe($artist);
						$t2->pending = 1;
						$t2->small = '';
						$t2->large = '';
						$t2->description = '';
						$t2->facebook = '';
						$t2->twitter = '';
						$t2->youtube = '';
						$db->store($t2);
					}
				}
			}
			
			$mixtape->user_id = $app->session['user']->id; //user who uploaded track
			
			foreach($optional as $k=>$v)
			{
				if(!isset($mixtape->{$k})){
					$mixtape->{$k} = '';
				}
			}
			
			list($mixtape->small,$mixtape->medium,$mixtape->large) = $this->square_thumbs('image_file',$app->files,[60,220,800],240,240);
			
			$files_uploaded[] = [
				'type'=>'image',
				'name'=>$mixtape->small
			];
			$files_uploaded[] = [
				'type'=>'image',
				'name'=>$mixtape->medium
			];
			$files_uploaded[] = [
				'type'=>'image',
				'name'=>$mixtape->large
			];
			
			$mixtape->approved    = 0;
			$mixtape->deleted      = 0;
			$mixtape->zip = '';
			$mixtape->added = time();
			$mixtape->approved = 0;
			$mixtape_id = $db->store($mixtape);
			$app->session['mixtape_id'] = $mixtape_id;
			
			$t3 = $db->model('views');
			$t3->mixtape_id = $mixtape_id;
			$t3->uniq = 'system_auto';
			$t3->timestamp = time();
			$db->store($t3);
			
			$t4 = $db->model('likes');
			$t4->mixtape_id = $mixtape_id;
			$t4->user_id  = 0;
			$t4->timestamp = time();
			$db->store($t4);
			
			$db->commit_tx();
		}
		catch(\exception $e){
			$db->rollback_tx();
			if(isset($mixtape_id)){
				@$db->delete('mixtape','id',$mixtape_id);
			}
			foreach($files_uploaded as &$file)
			{
				$file['name'] = array_pop(explode('/',$file['name']));
				$this->s3DeleteFile($file['type'],$file['name']);
			}
			throw $e;
		}
		return ['error'=>0,'message'=>$app->session['mixtape_id']];
	}
	
	public function mixtape_track()
	{
		$app = $this->app;
		$db  = $app->db;
		$post = $app->post;
		$filter = $app->filter;
		
		if(!$app->is_user()){
			throw new \exception('You must be logged in to create tracks.');
		}
		
		$user = $app->session['user'];
		
		if(!isset($app->session['mixtape_id'])){
			if(isset($post['mixtape_id'])){
				$mixtape = $db->model('mixtape',$post['mixtape_id']);
				//to prevent vulnerability where malicious user uploads a bad track to wipe out a mixtape, we give a 24 hour window and ensure mixtape hasn't been approved
				if($mixtape->user_id == $user->id && $mixtape->added > (time() - (24 * 60 * 60)) && $mixtape->approved == 0){
					$id = $post['mixtape_id'];
				}else{
					throw new \exception('invalid upload session');
				}
			}else{
				throw new \exception('invalid upload session');
			}
		}else{
			$id = $app->session['mixtape_id'];
		}
		
		
		$db->begin_tx();
		try{
			$mixtape = $db->model('mixtape',$id);
			$t = $db->model('track');
			$t->large  = '';
			$t->medium = '';
			$t->small  = '';
			$t->timestamp   = $mixtape->timestamp;
			$t->description = '';
			$t->youtube     = '';
			$t->purchase    = '';
			$t->user_id     = $mixtape->user_id;
			if(isset($post['artist']) && !empty($post['artist'])){
				$artist = $filter->rmnl($filter->min($post['artist']));
				if($db->exists('artist','name',$artist)){
					$t->artist_id = $db->idBy('artist','name',$artist);
				}else{
					$t2 = $db->model('artist');
					$t2->name = $artist;
					$t2->pending = 0;
					$t2->small = '';
					$t2->large = '';
					$t2->description = '';
					$t2->facebook = '';
					$t2->twitter = '';
					$t2->youtube = '';
					$t2->artist_id = $db->store($t2);
				}
			}else{
				$t->artist_id = $mixtape->artist_id;
			}
		
			$required = [
				'title'=>['min','rmnl'],
			];
			
			$filter->generate_model($t,$required,[],$post);
			$t->is_single    = 0; // true
			$t->is_mixtape   = 1; // false
			$t->approved    = 0;
			$t->deleted      = 0;
			$t->mixtape_id = $mixtape->id;
			$t->featuring = '';
			$checkboxes = [
				'download',
				'in_app'
			];
			foreach($checkboxes as $k){
				$t->{$k} = $mixtape->{$k};
			}
			
			if(!isset($app->files['audio'])){
				throw new \exception('You must select an Mp3 Audio File');
			}
			if($app->files['audio']['error'])
			{
				throw new \exception($this->upload_errors($app->files['audio_'.$i]['error']));
			}
			if( !is_uploaded_file($app->files['audio']['tmp_name']) ){
				throw new \exception('Bad file input');
			}
			if(!isset($post['hash'])){
				throw new \exception('Missing required upload data.');
			}
			$hash = $post['hash'];
			$computed_hash = md5(file_get_contents($app->files['audio']['tmp_name']));
			
			if( $hash !== $computed_hash ){
				throw new \exception('The computed hashes for a track do not match.');
			}
			
			$t->audio_file = $this->mp3_upload('audio',$app->files);
			$files_uploaded[] = [
				'type'=>'audio',
				'name'=>$t->audio_file
			];
			$db->store($t);
			$db->commit_tx();
		}
		catch(\exception $e){
			$db->commit_tx();
			if(isset($id)){
				$files = $db->getFilesForDelete('mixtape',$id);
				foreach($files['image'] as $image)
				{
					$this->s3DeleteFile('image',$image);
				}
				foreach($files['audio'] as $audio)
				{
					$this->s3DeleteFile('audio',$audio);
				}
				foreach($files['zip'] as $zip)
				{
					$this->s3DeleteFile('zip',$zip);
				}
				$db->delete('likes','mixtape_id',$id);
				$db->delete('views','mixtape_id',$id);
				$db->delete('favorites','mixtape_id',$id);
				$db->delete('featuredmixtape','mixtape_id',$id);
				$db->delete('track','mixtape_id',$id);
				$db->delete('mixtape','id',$id);
			}
			foreach($files_uploaded as &$file)
			{
				$file['name'] = array_pop(explode('/',$file['name']));
				$this->s3DeleteFile($file['type'],$file['name']);
			}
			throw $e;
		}
		return ['error'=>0,'message'=>1];
	}
	
}