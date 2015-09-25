<?php
namespace StarterKit;
class User
{
	public $id;
	public $user_name;
	public $email;
	public $password; //no we aren't storing it in plaintext; its a hash
	public $registered; //date user registered.
	public $small; //user avatar
	public $large; //user avatar
	public $banned;
	
	public $likes;
	public $favorites;
	
	public $sent = []; //well make sure user doesnt send more than 5 comments in a minute
	public $spam_flags = 0;
	
	function __construct($email,$pass,$remember=false)
	{
		$details = $this->fetch($email); //fetch user details. throws exception if email doesn't exist
		foreach($details as $k => $v){
			$this->{$k} = $v;
		}
		if($pass !== false && !$this->verify($pass)) { //passing false as the password allows bypassing the password check.
			throw new \exception('invalid username or password');
		}else{
			if($remember){
				$this->remember();
			}
			$this->likes();
			$this->faves();
			$this->save(); //save object to a session.
		}
	}
	
	public function likes()
	{
		$data = (\StarterKit\DB::getInstance())->getUserLikes($this->id);
		$likes = [
			'mixtape'=>[],
			'single'=>[]
		];
		foreach($data as $row){
			if(!empty($row['mixtape_id'])){
				array_push($likes['mixtape'],$row['mixtape_id']);
			}
			if(!empty($row['track_id'])){
				array_push($likes['single'],$row['track_id']);
			}
		}
		$this->likes = $likes;
	}
	
	public function faves()
	{
		$data = (\StarterKit\DB::getInstance())->getUserFavorites($this->id);
		$favorites = [
			'mixtape'=>[],
			'single'=>[]
		];
		foreach($data as $row){
			if(!empty($row['mixtape_id'])){
				array_push($favorites['mixtape'],$row['mixtape_id']);
			}
			if(!empty($row['track_id'])){
				array_push($favorites['single'],$row['track_id']);
			}
		}
		$this->favorites = $favorites;
	}
	
	public function rmlike($type,$id)
	{
		$key = array_search($id,$this->likes[$type]);
		if($key !== false){
			unset($this->likes[$type][$key]);
		}
	}
	public function rmfave($type,$id)
	{
		$key = array_search($id,$this->favorites[$type]);
		if($key !== false){
			unset($this->favorites[$type][$key]);
		}
	}
	public function addlike($type,$id)
	{
		array_push($this->likes[$type],$id);
	}
	public function addfave($type,$id)
	{
		array_push($this->favorites[$type],$id);
	}
	
	public function hasLike($type,$id)
	{
		return is_numeric(array_search($id,$this->likes[$type]));
	}
	public function hasFave($type,$id)
	{
		return is_numeric(array_search($id,$this->favorites[$type]));
	}
	
	public function remember()
	{
		$app = \StarterKit\App::getInstance();
		$db = $app->db;
		$restore = $db->model('userrestore');
		$restore->user_id = $this->id;
		$restore->email = $this->email;
		$data = $this->id . '//' . md5(time() . openssl_random_pseudo_bytes(16)) . '//' . $this->email;
		$restore->remote_addr = $secret = $app->remote_addr; //only allow session restore from same ip address. limit probability of exploit.
		$restore->hash = $hash = hash_hmac('sha512',$data,$secret);
		$db->store($restore);
		setcookie('sk_restore',$hash,time() + (30 * 86400) ,'/',$_SERVER['SERVER_NAME'],false,false);
	}
	
	public function save()
	{
		$_SESSION['user'] = &$this;
	}
	
	public function verify($val)
	{
		return password_verify($val, $this->password);
	}
	
	public function logout()
	{
		$db = (\StarterKit\App::getInstance())->db;
		$db->delete('userrestore','user_id',$this->id);
		$db->delete('useronline','user_id',$this->id);
		$_SESSION['user'] = null;
		unset($_SESSION['user']);
		setcookie('sk_restore','blank',time() - (30 * 86400) ,'/',$_SERVER['SERVER_NAME'],false,false);
	}
	
	public function fetch($email)
	{
		$db = \StarterKit\DB::getInstance();
		$details = $db->fetchUser($email);
		if(empty($details)){
			throw new \exception('invalid username or password');
		}else{
			return $details;
		}
	}
	
	public function update_password($current,$new)
	{
		$app = \StarterKit\App::getInstance();
		if(!$this->verify($current)){
			throw new \exception('Invalid Current Password');
		}
		$this->password = $app->filter->password_hash($new);
		$db = \StarterKit\DB::getInstance();
		$model = $db->model('user',$this->id);
		$db->updateColumn($model,'password',$this->password);
		$this->refresh(true);
		return true;
	}
	
	public function update_avatar($small,$large)
	{
		$app = \StarterKit\App::getInstance();
		$tools = new \StarterKit\FileTools;
		$tools->s3DeleteFile('image',array_pop(explode('/',$this->small)));
		$tools->s3DeleteFile('image',array_pop(explode('/',$this->large)));
		$this->small = $small;
		$this->large = $large;
		$db = $app->db;
		$model = $db->model('user',$this->id);
		$db->updateColumn($model,'small',$this->small);
		$db->updateColumn($model,'large',$this->large);
		$this->refresh(true);
		return true;
	}
	
	public static function restore($token)
	{
		$app = \StarterKit\App::getInstance();
		$success = false;
		$db = $app->db;
		$details = $db->fetchUserRestore($token);
		if(!empty($details)){
			if($app->remote_addr == $details['remote_addr']){
				$app->args['user'] = new self($details['email'],false,true);
				$db->trash('userrestore',$details['id']);
				$success = true;
			}
		}
		return $success;
	}
	
	public function refresh($force=false)
	{
		$details = (\StarterKit\DB::getInstance())->cachedCall('fetchUser',[$this->email],0,$force);
		if(!empty($details)){
			foreach($details as $k => $v){
				$this->{$k} = $v;
			}
		}
	}
	
	public function keepalive()
	{
		session_regenerate_id(true);
	}
	
	public function is_banned()
	{
		return (bool) $this->banned;
	}
	
	public function is_spamming()
	{
		
		if($this->banned == 0){
			$t =  time();
			$this->sent[] = $t;
			$this->sent = array_filter($this->sent, function ($x) use($t){ return $x >= $t - 60; });
			$result = count($this->sent) >= 5;
			if($result){
				$this->spam_flags += 1;
				if($this->spam_flags > 5){
					(\StarterKit\App::getInstance())->db->flagUserComments($this->id);
					$this->banned = 1; //soft bans. if they become a problem admin bans.
					return true;
				}
				return false;
			}	
		}else{
			return true;
		}
	}
}
