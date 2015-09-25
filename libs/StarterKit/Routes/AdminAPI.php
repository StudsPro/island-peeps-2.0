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
		if(!$this->app->is_admin()){
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
	
	public function keepalive()
	{
		$this->session['admin']->keepalive();
		return ['error'=>0,'message'=>1];
	}
	
	public function bulk_action()
	{
		$app = $this->app;
		$filter = $app->filter;
		$post = $app->post; 
		$db = $app->db;
		$args = $app->args;
		
		if(!isset($post['content'])){
			throw new \exception('missing content');
		}else{
			$content = $post['content'];
		}
		
		if(!isset($post['type'])){
			throw new \exception('missing type');
		}else{
			$type = $post['type'];
		}
		
		if(!isset($post['action'])){
			throw new \exception('missing type');
		}else{
			$action = $post['action'];
		}
		
		if(!isset($get['c'])){
			$c = false;
		}else{
			$c = $get['c'];
		}
		
		if(!isset($get['q'])){
			$q = false;
		}else{
			$q = $get['q'];
		}
		
		switch($content){
			case 'queue':
			
				switch($action){
					case 'approve':
						$key = 'approved';
						$val = 1;
					break;
					case 'pending':
						$key = 'approved';
						$val = 0;
					break;
					case 'trash':
						$key = 'deleted';
						$val = 1;
					break;
				}

				if(!empty($post['singles'])){
					$singles = rtrim($post['singles'],',');
					if(strpos($singles,',') !== false){
						$singles = explode(',',$singles);
					}else{
						$singles = [$singles]; //if its only one item it still must be an array
					}
					if($action == 'approve' || $action == 'pending'){
						$db->adminStatusUpdate('track',$singles,'deleted',0);
					}
					$db->adminStatusUpdate('track',$singles,$key,$val);
				}
				
				if(!empty($post['mixtapes'])){
					$mixtapes = rtrim($post['mixtapes'],',');
					if(strpos($mixtapes,',') !== false){
						$mixtapes = explode(',',$mixtapes);
					}else{
						$mixtapes = [$mixtapes];
					}
					if($action == 'approve' || $action == 'pending'){
						$db->adminStatusUpdate('mixtape',$mixtapes,'deleted',0);
					}
					$db->adminStatusUpdate('mixtape',$mixtapes,$key,$val);
				}
				
			break;
			
			case 'artists':
				switch($action){
					case 'approve':
						$key = 'pending';
						$val = 0;
					break;
					case 'pending':
						$key = 'pending';
						$val = 1;
					break;
					case 'trash':
						throw new \exception('Artists may not be deleted');
					break;
				}

				if(!empty($post['artists'])){
					$artists = rtrim($post['artists'],',');
					if(strpos($artists,',') !== false){
						$artists = explode(',',$artists);
					}else{
						$artists = [$artists]; //if its only one item it still must be an array
					}
					if($action == 'approve' || $action == 'pending'){
						$db->adminStatusUpdate('artist',$artists,'deleted',0);
					}
					$db->adminStatusUpdate('artist',$artists,$key,$val);
				}
			break;
			
			case 'comments':
				switch($action){
					case 'approve':
						$key = 'seen';
						$val = 1;
					break;
					case 'pending':
						$key = 'seen';
						$val = 0;
					break;
					case 'trash':
						$key = 'deleted';
						$val = 1;
					break;
				}

				if(!empty($post['comments'])){
					$comments = rtrim($post['comments'],',');
					if(strpos($comments,',') !== false){
						$comments = explode(',',$comments);
					}else{
						$comments = [$comments]; //if its only one item it still must be an array
					}
					if($action == 'approve' || $action == 'pending'){
						$db->adminStatusUpdate('comment',$comments,'deleted',0);
					}
					$db->adminStatusUpdate('comment',$comments,$key,$val);
				}
			break;
			
			case 'users':
				switch($action){
					case 'ban':
						$key = 'banned';
						$val = 1;
					break;
					case 'unban':
						$key = 'banned';
						$val = 0;
					break;
					case 'trash':
						$key = 'deleted';
						$val = 1;
					break;
					case 'untrash':
						$key = 'deleted';
						$val = 0;
					break;
				}
				if(!empty($post['users'])){
					$users = rtrim($post['users'],',');
					if(strpos($users,',') !== false){
						$users = explode(',',$users);
					}else{
						$users = [$users]; //if its only one item it still must be an array
					}
					if($action == 'unban'){
						$db->adminStatusUpdate('user',$users,'deleted',0);
					}
					$db->adminStatusUpdate('user',$users,$key,$val);
				}
			break;
		}
		
		switch(true){
			case ($content == 'queue' && $type == 'all'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue(false,false);
			break;
			case ($content == 'queue' && $type == 'approved'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue('approved',1);
			break;
			case ($content == 'queue' && $type == 'pending'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue('approved',0);
			break;
			case ($content == 'queue' && $type == 'trash'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue('deleted',1);
			break;
			
	
			case ($content == 'artists' && $type == 'all'):
				$key = 'artists';
				$template = 'admin_tbl2.twig';
				$args['artists'] = $db->getAArtists(false,false);
			break;
			case ($content == 'artists' && $type == 'published'):
				$key = 'artists';
				$template = 'admin_tbl2.twig';
				$args[$key] = $db->getAArtists('pending',0);
			break;
			case ($content == 'artists' && $type == 'pending'):
				$key = 'artists';
				$template = 'admin_tbl2.twig';
				$args[$key] = $db->getAArtists('pending',1);
			break;
			
			
			
			case ($content == 'comments' && $type == 'all'):
				$key = 'comments';
				$template = 'admin_tbl3.twig';
				$args[$key] = $db->getAComments(false,false);
			break;
			case ($content == 'comments' && $type == 'seen'):
				$key = 'comments';
				$template = 'admin_tbl3.twig';
				$args[$key] = $db->getAComments('seen',1);
			break;
			case ($content == 'comments' && $type == 'new'):
				$key = 'comments';
				$template = 'admin_tbl3.twig';
				$args[$key] = $db->getAComments('seen',0);
			break;
			
			
			
			case ($content == 'users' && $type == 'all'):
				$key = 'users';
				$template = 'admin_tbl4.twig';
				$args[$key] = $db->getAUsers(false,false);
			break;
			case ($content == 'users' && $type == 'banned'):
				$key = 'users';
				$template = 'admin_tbl4.twig';
				$args[$key] = $db->getAUsers('banned',1);
			break;
			case ($content == 'users' && $type == 'trash'):
				$key = 'users';
				$template = 'admin_tbl4.twig';
				$args[$key] = $db->getAUsers('deleted',1);
			break;
		}
		$response = [];
		$response['html'] = $this->getTwig()->render('partials/'.$template,$args);
		$response['pending'] = $this->app->db->countPending($content);
		$response['approved'] = $this->app->db->countApproved($content);
		
		return ['error'=>0,'message'=>$response];
	}
	
	public function update_counts()
	{
		$app = $this->app;
		$get = $app->get; 
		$db = $app->db;
		
		if(!isset($get['content'])){
			throw new \exception('missing content');
		}else{
			$content = $get['content'];
		}
		
		$response = [];
		$response['pending'] = $this->app->db->countPending($content);
		$response['approved'] = $this->app->db->countApproved($content);
		
		return ['error'=>0,'message'=>$response];
	}
	
	public function featured()
	{
		$app = $this->app;
		$get = $app->get; 
		$db = $app->db;
		$args = $app->args;
		
		$type = isset($get['type']) ? $get['type'] : false;
		$id   = isset($get['id']) ? $get['id'] : false;
		$toggle = isset($get['toggle']) ? $get['toggle'] : false;
		if(!$type || !$id || $toggle === false){
			throw new \exception('missing parameter');
		}
		$db->toggleFeatured($type,$id,$toggle);
		return ['error'=>0,'message'=>1];
	}
	
	public function approved()
	{
		$app = $this->app;
		$get = $app->get; 
		$db = $app->db;
		$args = $app->args;
		
		$type = isset($get['type']) ? $get['type'] : false;
		$id   = isset($get['id']) ? $get['id'] : false;
		$toggle = isset($get['toggle']) ? $get['toggle'] : false;
		if(!$type || !$id || $toggle === false){
			throw new \exception('missing parameter');
		}
		$db->toggleApproved($type,$id,$toggle);
		return ['error'=>0,'message'=>1];
	}
	
	public function paginate()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		$args = $app->args;
		
		if(!isset($get['content'])){
			throw new \exception('missing content');
		}else{
			$content = $get['content'];
		}
		
		if(!isset($get['type'])){
			throw new \exception('missing type');
		}else{
			$type = $get['type'];
		}
		if(!isset($get['page'])){
			throw new \exception('missing page');
		}else{
			$page = $get['page'];
		}
		
		if(!isset($get['c'])){
			$c = false;
		}else{
			$c = $get['c'];
		}
		
		if(!isset($get['q'])){
			$q = false;
		}else{
			$q = $get['q'];
		}
		
		switch(true){
			case ($content == 'queue' && $type == 'all'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue(false,false,$page,$c,$q);
			break;
			case ($content == 'queue' && $type == 'approved'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue('approved',1,$page,$c,$q);
			break;
			case ($content == 'queue' && $type == 'pending'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue('approved',0,$page,$c,$q);
			break;
			case ($content == 'queue' && $type == 'trash'):
				$key = 'queue';
				$template = 'admin_tbl1.twig';
				$args[$key] = $db->getAQueue('deleted',1,$page,$c,$q);
			break;
			
			
			
			case ($content == 'artists' && $type == 'all'):
				$key = 'artists';
				$template = 'admin_tbl2.twig';
				$args['artists'] = $db->getAArtists(false,false,$page,$q);
			break;
			case ($content == 'artists' && $type == 'published'):
				$key = 'artists';
				$template = 'admin_tbl2.twig';
				$args[$key] = $db->getAArtists('pending',0,$page,$q);
			break;
			case ($content == 'artists' && $type == 'pending'):
				$key = 'artists';
				$template = 'admin_tbl2.twig';
				$args[$key] = $db->getAArtists('pending',1,$page,$q);
			break;
			
			
			
			case ($content == 'comments' && $type == 'all'):
				$key = 'comments';
				$template = 'admin_tbl3.twig';
				$args[$key] = $db->getAComments(false,false,$page,$q);
			break;
			case ($content == 'comments' && $type == 'seen'):
				$key = 'comments';
				$template = 'admin_tbl3.twig';
				$args[$key] = $db->getAComments('seen',1,$page,$q);
			break;
			case ($content == 'comments' && $type == 'new'):
				$key = 'comments';
				$template = 'admin_tbl3.twig';
				$args[$key] = $db->getAComments('seen',0,$page,$q);
			break;
			
			case ($content == 'users' && $type == 'all'):
				$key = 'users';
				$template = 'admin_tbl4.twig';
				$args[$key] = $db->getAUsers(false,false,$page,$q);
			break;
			case ($content == 'users' && $type == 'banned'):
				$key = 'users';
				$template = 'admin_tbl4.twig';
				$args[$key] = $db->getAUsers('banned',1,$page,$q);
			break;
			case ($content == 'users' && $type == 'trash'):
				$key = 'comments';
				$template = 'admin_tbl4.twig';
				$args[$key] = $db->getAUsers('deleted',1,$page,$q);
			break;
			
			default:
				throw new \exception('unknown pagination type');
			break;
		}
		$html = $this->getTwig()->render('partials/'.$template,$args);
		return ['error'=>0,'message'=>['html'=>$html,'count'=>count($args[$key])]];
	}
	
	public function trash()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		
		if(!isset($get['id'])){
			throw new \exception('missing id');
		}
		if(!isset($get['type'])){
			throw new \exception('missing type');
		}
		
		if($get['type'] == 'comment'){
			$db->trash($get['type'],$get['id']);
		}else{
			$db->softDelete($get['type'],$get['id']);
		}
		return ['error'=>0,'message'=>1];
	}
	
	public function delete()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		
		if(!isset($get['id'])){
			throw new \exception('missing id');
		}
		if(!isset($get['type'])){
			throw new \exception('missing type');
		}
		
		if(in_array($get['type'],['track','mixtape'])){
			$files = $db->getFilesForDelete($get['type'],$get['id']);
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
			if($get['type'] == 'mixtape'){
				$db->delete('likes','mixtape_id',$get['id']);
				$db->delete('views','mixtape_id',$get['id']);
				$db->delete('favorites','mixtape_id',$get['id']);
				$db->delete('featuredmixtape','mixtape_id',$get['id']);
				$db->delete('track','mixtape_id',$get['id']);
				$db->delete('mixtape','id',$get['id']);
			}else{
				$db->delete('likes','track_id',$get['id']);
				$db->delete('views','track_id',$get['id']);
				$db->delete('favorites','track_id',$get['id']);
				$db->delete('featuredsingle','track_id',$get['id']);
				$db->delete('track','id',$get['id']);
			}
		}
		elseif($get['type'] == 'artist'){
			$artist = $db->model('artist',$get['id']);
			$db->rmCachedCall('artistProfile',[$artist->uri]);
			$db->trash('artist',$get['id']);
		}
		else{
			$db->trash($get['type'],$get['id']);
		}
		
		return ['error'=>0,'message'=>1];
	}
	
	
	public function ban()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		$args = $app->args;
		
		if(!isset($get['id'])){
			throw new \exception('missing id');
		}
		
		$db->banUser($get['id']);
		return ['error'=>0,'message'=>1];
	}
	
	public function unban()
	{
		$app = $this->app;
		$db  = $app->db;
		$get = $app->get;
		$args = $app->args;
		
		if(!isset($get['id'])){
			throw new \exception('missing id');
		}
		
		$db->unbanUser($get['id']);
		return ['error'=>0,'message'=>1];
	}
	
	public function edit_artist()
	{
		$app = $this->app;
		$filter = $app->filter;
		$post = $app->post; 
		$db = $app->db;
		$args = $app->args;
		
		$required = [
			'name'=>['rmnl','min'],
			'pending'=>'cast_int'
		];
		
		$optional = [
			'facebook'=>['rmnl','min'],
			'twitter'=>['rmnl','min'],
			'youtube'=>['rmnl','min'],
			'description'=>'min'
		];
		
		if(!isset($post['id'])){
			throw new \exception('missing id');
		}
		
		if(!$db->exists('artist','id',$post['id'])){
			throw new \exception('Invalid Artist id. no artist exists by this id');
		}
		
		$t = $db->model('artist',$post['id']);
		$filter->generate_model($t,$required,$optional,$post);
		
		try{
			//now try to see if image was uploaded
			if(isset($app->files['uploaded_image'])){
				$small = $t->small;
				$large  = $t->large;
				list($t->small,$t->medium,$t->large) = $this->square_thumbs('uploaded_image',$app->files);
				if(!empty($small) && $t->small !== $small && $t->small !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$small)));	
				}
				if(!empty($large)  && $t->large !== $medium && $t->large !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$large)));
				}	
			}
		}
		catch(\exception $e){
			
		}
		
		$db->store($t);
		$db->cachedCall('artistProfile',[$t->uri],0,true);//force update
		
		return ['error'=>0,'message'=>1];
		
	}
	
	
	public function edit_mixtape()
	{
		$app = $this->app;
		$filter = $app->filter;
		$post = $app->post; 
		$db = $app->db;
		$args = $app->args;
		
		$required = [
			'title'=>['min','rmnl'],
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
		
		if(!isset($post['id'])){
			throw new \exception('missing id');
		}
		
		if(!$db->exists('mixtape','id',$post['id'])){
			throw new \exception('Invalid Mixtape id. no mixtape exists by this id');
		}
		
		$t = $db->model('mixtape',$post['id']);
		
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
		
		
		foreach($checkboxes as $k){
			$t->{$k} = (int) isset($post[$k]) && $post[$k] == 1;
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
			if(isset($post[$k])){
				if(empty($post[$k])){
					$t->{$k} = '';
				}
			}
		}
			
		try
		{
			if(isset($app->files['uploaded_image'])){
				$small = $t->small;
				$medium = $t->medium;
				$large  = $t->large;
				list($t->small,$t->medium,$t->large) = $this->square_thumbs('uploaded_image',$app->files,[60,220,800],240,240);
				if(!empty($small) && $t->small !== $small && $t->small !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$small)));	
				}
				if(!empty($medium) && $t->medium !== $medium && $t->medium !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$medium)));
				}
				if(!empty($large)  && $t->large !== $medium && $t->large !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$large)));
				}	
			}
		}
		catch(\exception $e)
		{
			
		}
		$db->store($t);
		$db->cachedCall('getMixtape',[$t->id],30);
		return ['error'=>0,'message'=>1];
	}
	
	public function edit_track()
	{
		$app = $this->app;
		$filter = $app->filter;
		$post = $app->post; 
		$db = $app->db;
		$args = $app->args;
		
		if(!isset($post['id'])){
			throw new \exception('missing id');
		}
		
		if(!$db->exists('track','id',$post['id'])){
			throw new \exception('Invalid Track id. no track exists by this id');
		}
		
		$required = [
			'title'=>['min','rmnl'],
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
		
		$t = $db->model('track',$post['id']);
		
		
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
		
		
		foreach($checkboxes as $k){
			$t->{$k} = (int) isset($post[$k]) && $post[$k] == 1;
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
			if(isset($post[$k])){
				if(empty($post[$k])){
					$t->{$k} = '';
				}
			}
		}
		
		try{
			if(isset($app->files['uploaded_image'])){
				$small = $t->small;
				$medium = $t->medium;
				$large  = $t->large;
				list($t->small,$t->medium,$t->large) = $this->square_thumbs('uploaded_image',$app->files,[60,220,800],240,240);
				if(!empty($small) && $t->small !== $small && $t->small !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$small)));	
				}
				if(!empty($medium) && $t->medium !== $medium && $t->medium !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$medium)));
				}
				if(!empty($large)  && $t->large !== $medium && $t->large !== null){
					$this->s3DeleteFile('image',array_pop(explode('/',$large)));
				}	
			}
		}
		catch(\exception $e)
		{
			
		}
		$db->store($t);
		$db->cachedCall('getTrack',[$t->id],30);
		return ['error'=>0,'message'=>1];
	}
	
	public function change_banners()
	{
		
		$app = $this->app;
		$filter = $app->filter;
		$post = $app->post; 
		$db = $app->db;
		$args = $app->args;
		
		$required = [
			'desktop_url'=>'min',
			'mobile_url'=>'min'
		];
		
		if(!$db->exists('banner','id',1)){
			$t = $db->model('banner');
		}else{
			$t = $db->model('banner',1);
		}
		
		
		$filter->generate_model($t,$required,[],$post);
		
		try
		{
			$desktop = $t->desktop;
			$t->desktop = $this->img_upload('desktop',$app->files);
			if(!empty($desktop) && $t->desktop !== $desktop && $t->desktop !== null){
				$this->s3DeleteFile('image',array_pop(explode('/',$desktop)));
			}

		}
		catch(\exception $e)
		{
			
		}
		
		try
		{
			$mobile = $t->mobile;
			$t->mobile = $this->img_upload('mobile',$app->files);
			if(!empty($mobile) && $t->mobile !== $mobile && $t->mobile !== null){
				$this->s3DeleteFile('image',array_pop(explode('/',$mobile)));
			}
		}
		catch(\exception $e)
		{
			
		}
		
		$db->store($t);
		
		$db->cachedCall('getBanners',[],0,true);
		
		return ['error'=>0,'message'=>1];
		
	}
	
}