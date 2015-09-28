<?php

namespace StarterKit;

class Admin
{
	public $id;
	public $name;
	public $email;
	public $password; //no we aren't storing it in plaintext; its a hash
	public $avatar;
	
	//app specific thingies
	public $sidebar;
	public $order = '';
	public $menu = '';
	
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
	
	public function update()
	{
		$db = (\StarterKit\App::getInstance())->db;
		$t = $db->model('admin',$this->id);
		$self = get_object_vars($this);
		unset($self['id']);
		foreach($self as $k=>$v){
			$t->{$k} = $v;
		}
		$db->store($t);
		$this->buildMenu();
	}
	
	public function keepalive($token)
	{
		session_regenerate_id(true);
	}
	
	public function getMenu()
	{
		if(empty($this->menu)){
			$this->buildMenu();
		}
		return $this->menu;
	}
	
	private function buildMenu()
	{
		$items = [
			'
			<li data-order="0">
				<a href="{{base_url}}admin/dashboard">
					<img alt="Dashboard" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/home.png">
					<span>Dashboard</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="1" class="">
				<a href="{{base_url}}admin/masterlist">
					<img alt="Master List" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/sitemap.png">
					<span>Master List</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="2" class="">
				<a href="{{base_url}}admin/catmasterlist">
					<img alt="Master List" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/sitemap.png">
					<span>Master List Category</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="3" class="">
				<a href="{{base_url}}admin/mliststats">
					<img alt="Master List stats" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/banner.png">
					<span>Master List stats</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="4" class="">
				<a href="#">
					<img alt="Manage Polls" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/polls.png">
					<span>Manage Polls</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="5" class="">
				<a href="#suggestion-ui" data-toggle="collapse" data-parent="#social-sidebar-menu">
					<img alt="Suggestions" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/archives.png">
					<span>Suggestions</span><i class="fa arrow"></i>
				</a>
				<ul id="suggestion-ui" class="collapse">
					<li id=""><a href="{{base_url}}admin/suggestion">Suggestion</a></li>
					<li id=""><a href="{{base_url}}admin/suggestion/suggestemail">Email</a></li>
				</ul>
			</li>
			',
			'<li data-order="6" class="">
				<a href="{{base_url}}admin/pages">
					<img alt="about" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/about.png">
					<span>About</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="7" class="">
				<a href="{{base_url}}admin/banner">
					<img alt="Manage Banners" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/banner.png">
					<span>Manage Banners</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="8" class="">
				<a href="{{base_url}}admin/memelist">
					<img alt="Me Me Page" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/premium.png">
					<span>Me Me Page</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="9" class="">
				<a href="#affiliate-ui" data-toggle="collapse" data-parent="#social-sidebar-menu">
					<img alt="Affiliates" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/customers.png">
					<span>Affiliates</span>
					<span class="badge"></span>
					<i class="fa arrow"></i>
				</a>
				<ul id="affiliate-ui" class="collapse">
					<li id="">
						<a href="{{base_url}}admin/affiliate">Affiliate</a>
					</li>
					<li id="">
						<a href="{{base_url}}admin/affilateright">Permissions</a>
					</li>
				</ul>
			</li>
			',
			'
			<li data-order="10" class="">
				<a href="{{base_url}}admin/regions">
					<img alt="Country Profiles" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/country.png">
					<span>Country Profiles</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="11" class="">
				<a href="{{base_url}}admin/peopleprofile">
					<img alt="People Profiles" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/people.png">
					<span>People Profiles</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="12" class="">
				<a href="{{base_url}}admin/ads">
					<img alt="Manage Ads" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/ads.png">
					<span>Manage Ads</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="13" class="">
				<a href="{{base_url}}admin/emailtemplate">
					<img alt="Mail Templates" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/sitemap.png">
					<span>Mail Templates</span>
				</a>
			</li>
			',
			'
			<li data-order="14" class="">
				<a href="{{base_url}}admin/calendar">
					<img alt="Calendar" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/calendar.png">
					<span>Calendar</span><span class="badge">2</span>
				</a>
			</li>
			',
			'
			<li data-order="15" class="">
				<a href="{{base_url}}admin/stats">
					<img alt="Stats" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/statistics.png">
					<span>Stats</span>
				</a>
			</li>  
			'
		];
		if(empty($this->order)){
			$this->menu = implode('',$items);
		}else{
			$order = explode(',',$this->order);
			$this->menu = '';
			foreach($order as $k => $v)
			{
				$x = (int) $v;
				$this->menu .= isset($items[$x]) ? $items[$x] : '';
			}
		}
	}
}