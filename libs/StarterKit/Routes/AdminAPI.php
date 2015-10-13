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
					if(count($tmp) !== 13){
						throw new \exception('number of elements does not match specification.');
					}
					$all = range(0,12);
					if(sort($all) !== sort($tmp)){
						throw new \exception(json_encode([$all,$tmp]));
					}
				}
				$app->session['admin']->order = $v;
				$app->session['admin']->update();
			break;
			case 'theme':
			
				$v = isset($get['v']) ? $get['v'] : '';
				if(!empty($v)){
					if(in_array($v,['blue','green','orange','white'])){
						$app->session['admin']->theme = $v;
						$app->session['admin']->update();
					}
				}
			
			break;
			
			case 'perpage':
				$v = isset($get['v']) ? $get['v'] : '';
				if(!empty($v)){
					if(in_array($v,[10,25,50,100])){
						$app->session['admin']->perpage = $v;
						$app->session['admin']->update();
					}
				}
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
			$admin->avatar = $upl;
			if(!empty($img) && $admin->avatar !== $img && $upl !== null){
				$this->delFile($img);
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
	
	public function bulk_update()
	{
		$app = $this->app;
		$filter = $app->filter;
		$get = $app->get; 
		$db = $app->db;
		
		$required = ['table','column','value','ids'];
		
		foreach($required as $r)
		{
			if(!isset($get[$r]) || (isset($get[$r]) && empty($get[$r]))){
				throw new \exception('Missing required parameter '.$r);
			}
		}
		
		$tbl = $get['table'];
		$col = $get['column'];
		$val = $get['value'];
		$ids = $get['ids'];
		
		$allowed = ['masterlist'];
		
		if(!in_array($tbl,$allowed)){
			throw new \exception('You are not allowed to modify this table');
		}
		
		$ids = explode(',',$ids);
		
		if(!is_array($ids)){
			$ids = [$ids];
		}
		
		$db->updateColumnMulti($tbl,$col,$val,$ids);
		
		return ['error'=>0,'message'=>1];
	}
	
	public function profile()
	{
		$app = $this->app;
		$filter = $app->filter;
		$get = $app->get;
		$post = $app->post; 
		$db = $app->db;
		$admin = $app->session['admin'];
		
		//unset regions[] and replace with regions
		if(isset($post['regions[]'])){
			unset($post['regions[]']);
		}
		$post['regions'] = isset($_POST['regions']) ? $_POST['regions'] : [];
		
		$required = [
			'title'=>['min','rmnl'],
			'category_id'=>'cat_id',
			'tags'=>'tag_fm',
			'status'=>'status_fm',
			'regions'=>'region_fm',
		];
		
		$optional = [
			'month'=>'min',
			'day'=>'min',
			'year'=>'min',
			'description'=>'min',
			'youtube'=>'c_youtube',
			'fb_profile'=>'min',
			'fb_fanpage'=>'min',
			'tw_profile'=>'min',
			'tw_fanpage'=>'min',
			'tw_description'=>'c_twitter',
		];
		
		$filter->custom_filter('cat_id',function($input) use($db,$filter){
			$input = $filter->cast_int($input);
			if(!$db->exists('category','id',$input)){
				throw new \exception('invalid category');
			}
			return $input;
		});
		
		$filter->custom_filter('tag_fm',function($input) use($filter){
			$input = $filter->min($input);
			$input = explode(',',$input);
			$input = array_map('trim',$input);
			if(count($input) > 1){
				$input = implode(',',$input);
			}else{
				$input = $input[0];
			}
			return $input;
		});
		
		$filter->custom_filter('status_fm',function($input) use($filter){
			$input = $filter->cast_int($input);
			if(!in_array($input,[1,2,3,4])){
				throw new \exception('Invalid status');
			}
			return $input;
		});
		
		$filter->custom_filter('region_fm',function($input) use($filter,$db){
			if(!is_array($input)){
				throw new \exception('Invalid input format:: Regions');
			}
			$input = array_map([$filter,'cast_int'],$input);
			$x = count($input);
			$y = (int) $db->getCell('SELECT COUNT(id) FROM country WHERE id IN('.implode(',',$input).')');
			if($x !== $y){
				throw new \exception('1 or more regions is invalid X: '.$x.' Y: '.$y);
			}
			return implode(',',$input);
		});
		
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
		
		$filter->custom_filter('c_twitter',function($input) use($filter){
			$input = $filter->min($input);
			if(strlen($input) > 120){
				throw new \exception('Twitter Description may only contain a maximum of 120 charachters.');
			}
			return $input;
		});
		
		$id = isset($get['id']) ? $filter->cast_int($get['id']) : false;
		
		if($id){
			$t = $db->model('masterlist',$id);
			
			if( (int) $t->id !== $id){
				throw new \exception('a 1 ');
			}
			if( (int) $t->type_id !== 1){
				throw new \exception('a 2');
			}
		}else{
			$t = $db->model('masterlist');
		}
		
		$filter->generate_model($t,$required,$optional,$post);
		
		try{
			
			$t->img = $this->img_upload('uploaded_image',$app->files);
		}
		catch(\exception $e){
			
		}
		
		$t->type_id = 1;
		if(!isset($t->admin_id)){
			$t->admin_id = $admin->id;
		}
		$db->store($t);
		
		return ['error'=>0,'message'=>1];
	}
	
	public function country()
	{
		$app = $this->app;
		$filter = $app->filter;
		$get = $app->get;
		$post = $app->post; 
		$db = $app->db;
		$admin = $app->session['admin'];
		
		$required = [
			'name'=>'min',
			'title_banner'=>'min',
			'title'=>'min',
			'longitude'=>'min',
			'latitude'=>'min',
			'motto'=>'min',
			'anthem'=>'min',
			'national_dish'=>'min',
			'capital'=>'min',
			'language'=>'min',
			'population'=>'min',
			'status'=>'status_fm'
		];
		
		$optional = [
			'month'=>'min',
			'day'=>'min',
			'year'=>'min',
			'description'=>'min'
		];
		
		$filter->custom_filter('status_fm',function($input) use($filter){
			$input = $filter->cast_int($input);
			if(!in_array($input,[1,2,3,4])){
				throw new \exception('Invalid status');
			}
			return $input;
		});
		
		$id = isset($get['id']) ? $filter->cast_int($get['id']) : false;
		
		if($id){
			$t = $db->model('country',$id);
			
			if( (int) $t->id !== $id){
				throw new \exception('a 1 ');
			}

		}else{
			$t = $db->model('country');
		}
		
		$filter->generate_model($t,$required,$optional,$post);
		
		
		$images = [
			'img_file'=>'img',
			'map_file'=>'map_img',
			'flag_file'=>'flag_img',
			'cover_file'=>'cover_img'
		];
		
		
		foreach($images as $k=>$v){
			try{
				$img = $t->{$v};
				$ignore_trans = ($k == 'map_file') ? false : true;
				$upl = $this->img_upload($k,$app->files,1,1,$ignore_trans);
				$t->{$v} = $upl;
				if(!empty($img) && $t->{$v} !== $img && $upl !== null){
					$this->delFile($img);
				}	
			}
			catch(\exception $e){}
		}
		if(!isset($t->admin_id)){
			$t->admin_id = $admin->id;
		}
		$db->store($t);
		return ['error'=>0,'message'=>1];
	}
	
	public function meme()
	{
		$app = $this->app;
		$filter = $app->filter;
		$get = $app->get;
		$post = $app->post; 
		$db = $app->db;
		$admin = $app->session['admin'];
		
		$required = [
			'title'=>'min',
		];
		
		$optional = [
		];
		
		$id = isset($get['id']) ? $filter->cast_int($get['id']) : false;
		
		if($id){
			$t = $db->model('masterlist',$id);
			
			if( (int) $t->id !== $id){
				throw new \exception('a 1 ');
			}
			
			if( (int) $t->type_id !== 2){
				throw new \exception('a 2');
			}

		}else{
			$t = $db->model('country');
		}
		
		$filter->generate_model($t,$required,$optional,$post);
		
		
		$images = [
			'uploaded_image'=>'img',
		];
		
		
		foreach($images as $k=>$v){
			try{
				$img = $t->{$v};
				$upl = $this->img_upload($k,$app->files,1,1);
				$t->{$v} = $upl;
				if(!empty($img) && $t->{$v} !== $img && $upl !== null){
					$this->delFile($img);
				}	
			}
			catch(\exception $e){}
		}
		if(!isset($t->admin_id)){
			$t->admin_id = $admin->id;
		}
		$t->type_id = 2;
		$db->store($t);
		return ['error'=>0,'message'=>1];
	}
	
	public function funfact()
	{
		$app = $this->app;
		$filter = $app->filter;
		$get = $app->get;
		$post = $app->post; 
		$db = $app->db;
		$admin = $app->session['admin'];
		
		//unset regions[] and replace with regions
		if(isset($post['regions[]'])){
			unset($post['regions[]']);
		}
		$post['regions'] = isset($_POST['regions']) ? $_POST['regions'] : [];
		
		$required = [
			'title'=>['min','rmnl'],
			'tags'=>'tag_fm',
			'status'=>'status_fm',
			'regions'=>'region_fm',
		];
		
		$optional = [
			'description'=>'min',
			'youtube'=>'c_youtube',
			'tw_description'=>'c_twitter',
		];
		
		$filter->custom_filter('tag_fm',function($input) use($filter){
			$input = $filter->min($input);
			$input = explode(',',$input);
			$input = array_map('trim',$input);
			if(count($input) > 1){
				$input = implode(',',$input);
			}else{
				$input = $input[0];
			}
			return $input;
		});
		
		$filter->custom_filter('status_fm',function($input) use($filter){
			$input = $filter->cast_int($input);
			if(!in_array($input,[1,2,3,4])){
				throw new \exception('Invalid status');
			}
			return $input;
		});
		
		$filter->custom_filter('region_fm',function($input) use($filter,$db){
			if(!is_array($input)){
				throw new \exception('Invalid input format:: Regions');
			}
			$input = array_map([$filter,'cast_int'],$input);
			$x = count($input);
			$y = (int) $db->getCell('SELECT COUNT(id) FROM country WHERE id IN('.implode(',',$input).')');
			if($x !== $y){
				throw new \exception('1 or more regions is invalid X: '.$x.' Y: '.$y);
			}
			return implode(',',$input);
		});
		
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
		
		$filter->custom_filter('c_twitter',function($input) use($filter){
			$input = $filter->min($input);
			if(strlen($input) > 120){
				throw new \exception('Twitter Description may only contain a maximum of 120 charachters.');
			}
			return $input;
		});
		
		$id = isset($get['id']) ? $filter->cast_int($get['id']) : false;
		
		if($id){
			$t = $db->model('masterlist',$id);
			
			if( (int) $t->id !== $id){
				throw new \exception('a 1 ');
			}
			if( (int) $t->type_id !== 3){
				throw new \exception('a 2');
			}
		}else{
			$t = $db->model('masterlist');
		}
		
		$filter->generate_model($t,$required,$optional,$post);
		
		try{
			
			$t->img = $this->img_upload('uploaded_image',$app->files);
		}
		catch(\exception $e){
			
		}
		
		$t->type_id = 3;
		if(!isset($t->admin_id)){
			$t->admin_id = $admin->id;
		}
		$db->store($t);
		
		return ['error'=>0,'message'=>1];
	}
	
	public function chat_log()
	{
		$app = $this->app;
		$db = $app->db;
		return ['error'=>0,'message'=>$db->chatLog()];
	}
	
	public function chat_latest()
	{
		$app = $this->app;
		$db = $app->db;
		$get = $app->get;
		$last_id = isset($get['last_id']) ? $get['last_id'] : false;
		if(!$last_id){
			return ['error'=>1,'message'=>'invalid request'];
		}
		return ['error'=>0,'message'=>$db->chatUpdate($last_id)];
	}
	
	public function chat_post()
	{
		$app = $this->app;
		$db = $app->db;
		$get = $app->get;
		$msg = isset($get['msg']) ? $get['msg'] : false;
		if(!empty($msg)){
			$filter = $app->filter;
			$t = $db->model('chat');
			$t->admin_id = $app->session['admin']->id;
			$t->timestamp = time();
			$t->message = $filter->min($msg);	
			$id = $db->store($t);
		}
		return ['error'=>0,'message'=>$id];
	}
	
	public function chat_delete()
	{
		$app = $this->app;
		$db = $app->db;
		$get = $app->get;
		$id = isset($get['id']) ? $get['id'] : false;
		if(!$id){
			throw new \exception('missing chat id');
		}
		$db->delete('chat','id',$id);	
		return ['error'=>0,'message'=>1];
	}
	
	public function about()
	{
		$app = $this->app;
		$filter = $app->filter;
		$get = $app->get;
		$post = $app->post; 
		$db = $app->db;
		$admin = $app->session['admin'];
		
		$id = isset($get['id']) ? $filter->cast_int($get['id']) : false;
		
		if($id){
			$t = $db->model('about',$id);
			
			if( (int) $t->id !== $id){
				throw new \exception('a 1 ');
			}
		}else{
			$t = $db->model('about');
		}
		
		$required = [
			'title'=>['min','rmnl'],
			'content'=>'c_unsafe',
			'status'=>'c_status'
		];
		
		$filter->custom_filter('c_unsafe',function($input){return $input;});
		
		$filter->custom_filter('c_status',function($input) use($filter){
			$input = $filter->cast_int($input);
			if(!in_array($input,[0,1])){
				throw new \exception('invalid status');
			}
			return $input;
		});
		
		$filter->generate_model($t,$required,[],$post);
		
		$db->store($t);
		
		return ['error'=>0,'message'=>1];
	}
	
	public function advertisement()
	{
		$app = $this->app;
		$filter = $app->filter;
		$get = $app->get;
		$post = $app->post; 
		$db = $app->db;
		$admin = $app->session['admin'];
		
		$id = isset($get['id']) ? $filter->cast_int($get['id']) : false;
		
		if($id){
			$t = $db->model('ad',$id);
			
			if( (int) $t->id !== $id){
				throw new \exception('a 1 ');
			}
		}else{
			$t = $db->model('ad');
		}
		
		$required = [
			'title'=>['min','rmnl'],
			'regions'=>'region_fm',
			'type'=>'type_fm',
			'size'=>'size_fm'
		];
		
		$filter->custom_filter('type_fm',function($input){
			if(!in_array($input,['video','image'])){
				throw new \exception('Invalid option present in form. (Ad Type)');
			}
			return $input;
		});
		
		$filter->custom_filter('size_fm',function($input){
			if(!in_array($input,['ad-lg','ad-fw'])){
				throw new \exception('Invalid option present in form (Ad Size)');
			}
			return $input;
		});
		
		
		
		$filter->custom_filter('region_fm',function($input) use($filter,$db){
			if(!is_array($input)){
				throw new \exception('Invalid input format:: Regions');
			}
			$input = array_map([$filter,'cast_int'],$input);
			$x = count($input);
			$y = (int) $db->getCell('SELECT COUNT(id) FROM country WHERE id IN('.implode(',',$input).')');
			if($x !== $y){
				throw new \exception('1 or more regions is invalid X: '.$x.' Y: '.$y);
			}
			return implode(',',$input);
		});
		
		$optional = [
			'url'=>'min',
			'desc'=>'min'
		];
		
		$filter->generate_model($t,$required,$optional,$post);
		
		if($t->type == 'image'){
			try{
				$t->image = $this->img_upload('uploaded_image',$app->files);
			}
			catch(\exception $e){
				if(!$id){
					throw $e;
				}
			}
		}else{
			try{
				$t->video = $this->video_upload('uploaded_video',$app->files);
			}
			catch(\exception $e){
				if(!$id){
					throw $e;
				}
			}
		}
		
		$db->store($t);
		
		return ['error'=>0,'message'=>1];
	}

} 