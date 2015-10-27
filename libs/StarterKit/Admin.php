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
	public $permissions = [];
	public $theme = '';
	public $perpage = 100;
	
	public $dashboard = '';
	public $dashboard_order='';
	
	public $stats = '';
	public $stats_order = '';
	
	public $mlist_stats = '';
	public $mlist_order = '';
	
	function __construct($email,$pass,$remember=false)
	{
		$details = $this->fetch($email); //fetch user details. throws exception if email doesn't exist
		foreach($details as $k => $v){
			if($k=='permissions'){
				$this->{$k} = json_decode($v,true);
			}else{
				$this->{$k} = $v;
			}
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
		$details = $db->cachedCall('fetchAdmin',[$email]);
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
	
	public function refresh()
	{
		$details = $this->fetch($this->email); //fetch user details. throws exception if email doesn't exist
		foreach($details as $k => $v){
			if($k=='permissions'){
				$this->{$k} = json_decode($v,true);
			}else{
				$this->{$k} = $v;
			}
		}
	}
	
	public function update()
	{
		$db = (\StarterKit\App::getInstance())->db;
		$this->refresh();
		$t = $db->model('admin',$this->id);
		$self = get_object_vars($this);
		unset($self['id'],$self['menu'],$self['permissions'],$self['dashboard'],$self['stats'],$self['mlist_stats']);
		foreach($self as $k=>$v){
			if($k=='permissions'){
				$t->{$k} = json_encode($v);
			}else{
				$t->{$k} = $v;
			}
		}
		$db->store($t);
		$this->buildMenu();
		$this->buildDashboard();
		$this->buildStats();
		$this->buildMasterlistStats();
	}
	
	public function keepalive($token)
	{
		session_regenerate_id(true);
	}
	
	public function can($module,$action)
	{
		return $this->permissions[$module][$action] === 1;
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
				<a href="#masterlist-ui" data-toggle="collapse" data-parent="#social-sidebar-menu">
					<img alt="Master List" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/sitemap.png">
					<span>Master List</span>
					<i class="fa arrow"></i>
				</a>
				<ul id="masterlist-ui" class="collapse">
					<li id=""><a href="{{base_url}}admin/masterlist">All</a></li>
					<li id=""><a href="{{base_url}}admin/masterlist?type_id=1">People</a></li>
					<li id=""><a href="{{base_url}}admin/masterlist?type_id=2">Memes</a></li>
					<li id=""><a href="{{base_url}}admin/masterlist?type_id=3">Fun Facts</a></li>
				</ul>
			</li>
			',
			'
			<li data-order="2" class="">
				<a href="{{base_url}}admin/masterlist_stats">
					<img alt="Master List stats" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/banner.png">
					<span>Master List stats</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="3" class="">
				<a href="{{base_url}}admin/polls">
					<img alt="Manage Polls" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/polls.png">
					<span>Manage Polls</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="4" class="">
				<a href="#suggestion-ui" data-toggle="collapse" data-parent="#social-sidebar-menu">
					<img alt="Suggestions" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/archives.png">
					<span>Suggestions</span><i class="fa arrow"></i>
				</a>
				<ul id="suggestion-ui" class="collapse">
					<li id=""><a href="{{base_url}}admin/suggestion">Suggestion</a></li>
					<li id=""><a href="{{base_url}}admin/suggestion?by_email">Email</a></li>
				</ul>
			</li>
			',
			'<li data-order="5" class="">
				<a href="{{base_url}}admin/about">
					<img alt="about" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/about.png">
					<span>About</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="6" class="">
				<a href="{{base_url}}admin/banners">
					<img alt="Manage Banners" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/banner.png">
					<span>Manage Banners</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="7" class="">
				<a href="{{base_url}}admin/affiliates">
					<img alt="Affiliates" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/customers.png">
					<span>Affiliates</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="8" class="">
				<a href="{{base_url}}admin/countries">
					<img alt="Country Profiles" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/country.png">
					<span>Country Profiles</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="9" class="">
				<a href="{{base_url}}admin/ads">
					<img alt="Manage Ads" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/ads.png">
					<span>Manage Ads</span>
					<span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="10" class="">
				<a href="{{base_url}}admin/mail_templates">
					<img alt="Mail Templates" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/sitemap.png">
					<span>Mail Templates</span>
				</a>
			</li>
			',
			'
			<li data-order="11" class="">
				<a href="{{base_url}}admin/calendar">
					<img alt="Calendar" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/calendar.png">
					<span>Calendar</span><span class="badge"></span>
				</a>
			</li>
			',
			'
			<li data-order="12" class="">
				<a href="{{base_url}}admin/stats">
					<img alt="Stats" src="{{base_url}}static/adm/stuttgart-icon-pack/32x32/statistics.png">
					<span>Analytics/Stats</span>
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
	
	public function getDashboard()
	{
		if(empty($this->dashboard)){
			$this->buildDashboard();
		}
		return $this->dashboard;
	}
	
	private function buildDashboard()
	{
		$items = [
			'
			<div class="col-md-6 dashclass span2" id="rdashboard" data-order="0">
				<div class="panel panel-default panel_dashboard">
					<div class="panel-heading">
						<h3 class="panel-title"><i class="fa fa-desktop"></i>Dashboard Notification</h3>
					</div>
					<div class="panel-body maxheight">The aliens are watching me</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 dashclass span2" id="rrecent" data-order="1">
				<div class="panel panel-primary panel-recent" >
					<div class="panel-heading" id="recent">
						<div class="panel-title recent-title"><i class="fa fa-list"></i> Recent </div>
						<!-- //Notice .panel-tools class-->
						<div class="panel-tools pull-right recent-tabs">
						<ul class="nav nav-tabs">
							<li class="active">
							<a data-toggle="tab" href="#tab_home" aria-expanded="true">Profiles</a>
							</li>
							<li class="">
							<a data-toggle="tab" href="#tab_profile" aria-expanded="tryue">Affiliates Activities</a>
							</li>
						</ul>
						</div>
					</div>
					<div class="panel-body full_width padding_left_right_zero">
						<div class="tab-content full_width">
							<div id="tab_home" class="tab-pane active users-feed userprofile">
								<div class="scroll maxheight_recent">
								
								</div>
							</div>
							<div id="tab_profile" class="tab-pane  activities-feed">
								<div class="scroll maxheight_recent">
									
								</div>
							</div>
					</div>
				</div>
			</div>
		</div>
		',
		'
		<div class="col-md-6 dashclass span2" id="rmap" data-order="2">
			<div class="panel panel_location">
				<div class="panel-heading panel-success">
					<div class="panel-title"><i class="icon-map-marker"></i>&nbsp;Visits by location</div>
				</div>
				<div class="panel-body scroll maxheight_visit">
					 <div id="vmap-world" class="vmap" style="width: 500px; height: 450px;"></div>
				</div>
			</div>
		</div> 
		',
		'
		<div class="col-md-6 dashclass span2" id="rcalendar" data-order="3">
			<div class="panel panel-default panel-calendar">
				<div class="panel-heading">
					<h3 class="panel-title"><i class="fa fa-calendar"></i>Calendar</h3>
				</div>
				<div class="panel-body scroll maxheight  maxheight_calender">
					<div class="col-md-12">
							<div id="calendar-min" style="width:100%"></div>
						</div>
				</div>
			</div>
		</div>
		',
		'
		<div class="col-md-12 dashclass span2" id="rvistspercountry" data-order="4">
			<div class="panel panel-primary panel_tracking ">
				<div class="panel-heading">
					<div class="panel-title">Visits by Country</div>
				</div>
				<div class="panel-body maxheight_resouce remove-lpadding">
					<div id="pie-visitsperc" style="height:500px" class="plot"></div>
				</div>
			</div>
		</div>
		',
		'
		<div class="col-md-12 dashclass span2" id="rprofilepercountry" data-order="5">
			<div class="panel panel-primary panel_tracking ">
				<div class="panel-heading">
					<div class="panel-title">Profile Per Country</div>
				</div>
				<div class="panel-body maxheight_resouce remove-lpadding" >
					<div id="pie-profileperc" style="height:500px" class="plot"></div>
				</div>
			</div>
		</div>
		',
		'
		<div class="col-md-12 dashclass span2" id="" data-order="6">
			<div class="panel panel-primary panel_tracking ">
				<div class="panel-heading">
				<div class="panel-title">Website Visits</div>
				</div>
				<div class="panel-body maxheight_resouce">
				<div id="demo-plot" style="height:500px" class="plot"></div>
				</div>
			</div>
		</div>
		'
		];
		if(empty($this->dashboard_order)){
			$this->dashboard = implode('',$items);
		}else{
			$order = explode(',',$this->dashboard_order);
			$this->dashboard = '';
			foreach($order as $k => $v)
			{
				$x = (int) $v;
				$this->dashboard .= isset($items[$x]) ? $items[$x] : '';
			}
		}
	}
	
	public function getStats()
	{
		if(empty($this->stats)){
			$this->buildStats();
		}
		return $this->stats;
	}
	
	private function buildStats()
	{
		$items = [
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsyearlywiseinfo" data-order="0">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title"> Yearly Hits</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-yearlydiv" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsmonthlywiseinfo" data-order="1">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title"> Month (Oct) Hits</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-monthlydiv" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsdaywiseinfo" data-order="2">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title"> Days Hits</div>
					</div>
					<div class="panel-body maxheight">
						<div id="piedaysdiv" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsvistsonmap" data-order="3">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title">Visits on Map</div>
					</div>
					<div class="panel-body maxheight">
						<div id="vmap-world" class="vmap" style="width: 450px; height: 450px; position: relative; overflow: hidden;">
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsnewsreturn" data-order="4">
				<div class="panel panel-warning">
					<div class="panel-heading ">
						<div class="panel-title">&nbsp;New vs Returning</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-usertype1" class="plot" style="height:500px"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statstopcounterys" data-order="5">
				<div class="panel panel-warning  ">
					<div class="panel-heading ">
						<div class="panel-title">&nbsp;Top Countries(Visits)</div>
					</div>
					<div style="overflow: hidden; width: auto; height: 500px;" class="panel-body maxheight">
						<div id="pie-country" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statstopcitys" data-order="6">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title">Top Cities(Visits)</div>
					</div>
					<div class="panel-body maxheight">
						<div class="plot" style="height:500px" id="pie-city"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsvistbybrowser" data-order="7">
				<div class="panel panel-warning  ">
					<div class="panel-heading ">
						<div class="panel-title">&nbsp; Visits by Browser</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-browser" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsvistbyos" data-order="8">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title">Visits by Operating System</div>
					</div>
					<div class="panel-body maxheight">
						<div class="plot" style="height:500px" id="pie-os"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsvistbyscreenr" data-order="9">
				<div class="panel panel-warning  ">
					<div class="panel-heading ">
						<div class="panel-title">&nbsp; Visits by Screen Regulation</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-screen" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsvistbyservicepro" data-order="10">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title"> Visits by Service provider</div>
					</div>
					<div class="panel-body maxheight">
						<div class="plot" style="height:500px" id="pie-isp"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsreferralsoutracking" data-order="11">
				<div class="panel panel-warning  ">
					<div class="panel-heading ">
						<div class="panel-title">&nbsp; Referral source Tracking</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-chart" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statssocialnetworkref" data-order="12">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title"> Social Network Referrals</div>
					</div>
					<div class="panel-body maxheight">
						<div class="plot" style="height:500px" id="pie-social"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statspagetracking" data-order="13">
				<div class="panel panel-warning  ">
					<div class="panel-heading ">
						<div class="panel-title">&nbsp; <i class="icon-bar-chart"></i> Page Tracking</div>
					</div>
					<div class="panel-body maxheight">
						<div id="demo-plot" class="plot" style="height:500px"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsdeviceinfo" data-order="14">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title"> Device Info</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-device" class="plot" style="height:500px"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statskeywords" data-order="15">
				<div class="panel panel-warning  ">
					<div class="panel-heading ">
						<div class="panel-title">&nbsp; Keywords</div>
					</div>
					<div class="panel-body maxheight">
						<div class="plot" style="height:500px" id="donut-chart"></div>
					</div>
				</div>
			</div>
			',
			'
			<div class="col-md-6 span2 ui-sortable-handle" id="statsmobilediveinfo" data-order="16">
				<div class="panel panel-primary ">
					<div class="panel-heading">
						<div class="panel-title"> Mobile Device Info</div>
					</div>
					<div class="panel-body maxheight">
						<div id="pie-mobdiv" style="height:500px" class="plot"></div>
					</div>
				</div>
			</div>
			'
		];
		if(empty($this->stats_order)){
			$this->stats = implode('',$items);
		}else{
			$order = explode(',',$this->stats_order);
			$this->stats = '';
			foreach($order as $k => $v)
			{
				$x = (int) $v;
				$this->stats .= isset($items[$x]) ? $items[$x] : '';
			}
		}
	}
	
	public function getMasterlistStats()
	{
		if(empty($this->mlist_stats)){
			$this->buildMasterlistStats();
		}
		return $this->mlist_stats;
	}
	
	private function buildMasterlistStats()
	{
		$items = [
			'
			<div style="" class="row span2 ui-sortable-handle" id="mlistcategory" data-order="0">
				<div class="col-md-12">
					<div class="panel panel-default  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Category</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-4">
								<select name="category" class="form-control input-md" id="mlist-category-select" style="margin-bottom: 30px;">
								</select>
							</div>
							<div class="col-md-12">
								<div id="pie-cat" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistactors" data-order="1">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Actors</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-actors" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistsinger" data-order="2">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Singer</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-singer" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistathletes" data-order="3">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Athletes</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-athletes" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistpoliticians" data-order="4">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Politicians</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-politicians" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistgangsters" data-order="5">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Gangsters</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-gangsters" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistauthors" data-order="6">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Authors</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-authors" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistprofilepercountry" data-order="7">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Profiles Per Country</div>
						</div>
						<div class="panel-body propercmaxheight">
							<div class="col-md-12">
								<div id="pie-properc" class="plot" style="height: 600px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistprofilebyaffiliate" data-order="8">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Profile upload by Affiliate</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-profilebyadmin" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistmasterlistprofile" data-order="9">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Master List Profiles</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-masterlists" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistprofilestatus" data-order="10">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Profile Status</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-profilestatus" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistsuggestionkind" data-order="11">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Suggestion Profile</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-suggestionkind" class="plot" style="height: 300px; overflow: hidden;"></div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistsuggestionpercountry" data-order="12">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Suggestion Per Country</div>
						</div>
						<div class="panel-body propercmaxheight">
							<div class="col-md-12">
								<div id="pie-suggestion" class="plot" style="height: 600px; overflow: hidden;">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistbirthbymonth" data-order="13">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Birth By Month</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-profilebob" class="plot" style="height: 300px; overflow: hidden;">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			',
			'
			<div class="row span2 ui-sortable-handle" id="mlistsuggestiontopemail" data-order="14">
				<div class="col-md-12">
					<div class="panel panel-primary  ">
						<div class="panel-heading ">
							<div class="panel-title">&nbsp;Top 10 Suggestions</div>
						</div>
						<div class="panel-body maxheight">
							<div class="col-md-12">
								<div id="pie-suggestiontopemail" class="plot" style="height: 300px; overflow: hidden;">
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			'
		];
		if(empty($this->mlist_order)){
			$this->mlist_stats = implode('',$items);
		}else{
			$order = explode(',',$this->mlist_order);
			$this->mlist_stats = '';
			foreach($order as $k => $v)
			{
				$x = (int) $v;
				$this->mlist_stats .= isset($items[$x]) ? $items[$x] : '';
			}
		}
	}
}