<?php

namespace StarterKit;

class Admin
{
	public $id;
	public $name;
	public $email;
	public $password; //no we aren't storing it in plaintext; its a hash
	
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
			$this->save(); //save object to a session.
		}
	}
	
	public function remember()
	{
		$app = \StarterKit\App::getInstance();
		$db = $app->db;
		$restore = $db->model('adminrestore');
		$restore->admin_id = $this->id;
		$restore->email = $this->email;
		$data = $this->id . '//' . md5(time() . openssl_random_pseudo_bytes(16)) . '//' . $this->email;
		$restore->remote_addr = $secret = $app->remote_addr; //only allow session restore from same ip address. limit probability of exploit.
		$restore->hash = $hash = hash_hmac('sha512',$data,$secret);
		$db->store($restore);
		setcookie('__restore',$hash,time() + (30 * 86400) ,'/',$_SERVER['SERVER_NAME'],false,false);
	}
	
	public function save()
	{
		$_SESSION['admin'] = &$this;
	}
	
	public function verify($val)
	{
		return password_verify($val, $this->password);
	}
	
	public function logout()
	{
		$db = (\StarterKit\App::getInstance())->db;
		$db->delete('adminrestore','admin_id',$this->id);
		$_SESSION['admin'] = null;
		unset($_SESSION['admin']);
		setcookie('__restore','blank',time() - (30 * 86400) ,'/',$_SERVER['SERVER_NAME'],false,false);
	}
	
	public function fetch($email)
	{
		$db = \StarterKit\DB::getInstance();
		$details = $db->fetchAdmin($email);
		if(empty($details)){
			throw new \exception('invalid username or password');
		}else{
			return $details;
		}
	}
	
	public static function restore($token)
	{
		$app = \StarterKit\App::getInstance();
		$success = false;
		$db = $app->db;
		$details = $db->fetchAdminRestore($token);
		if(!empty($details)){
			if($app->remote_addr == $details['remote_addr']){
				new self($details['email'],false,true);
				$db->trash('adminrestore',$details['id']);
				$success = true;
			}
		}
		return $success;
	}
	
	public function keepalive($token)
	{
		session_regenerate_id(true);
	}
}