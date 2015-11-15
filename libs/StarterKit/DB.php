<?php

namespace StarterKit;

class DB
{
	
	private static $instance = null;
	
	protected function __construct($config)
	{
		class_alias('\RedBeanPHP\R','\R');
		if(empty($config)){
			throw new \exception('missing db config');
		}
		$this->configure_redbean($config);
	}
	
	public function __call($method,$args=[])
	{
		try{
			return call_user_func_array(['\R',$method],$args);
		}
		catch(\exception $e)
		{
			throw $e;
		}
	}
	
	public static function getInstance($config = false)
	{
		return (is_null(self::$instance) ? self::$instance = new self($config) : self::$instance);
	}
	
	public function configure_redbean($c)
	{
		\R::setup('mysql:host='.$c['host'].';dbname='.$c['name'],$c['user'],$c['pass']);
		if(method_exists('\\R','setAutoResolve')){
			\R::setAutoResolve( true );
		}
	}
	
	public function model($type,$id=false)
	{
		if($id !== false){
			$model = \R::load($type,$id);
		}else{
			$model = \R::dispense($type);
		}
		return $model;
	}
	
	public function trash($type,$id)
	{
		return \R::trash( \R::load($type,$id) );
	}
	
	public function store($model)
	{
		$id = \R::store($model);
		return $id;
	}
	
	public function begin_tx()
	{
		\R::begin();
	}
	
	public function commit_tx()
	{
		\R::commit();
	}
	
	public function rollback_tx()
	{
		\R::rollback();
	}
	
	public function cachedCall($call,$args = [],$expiry = 0,$force = false,$touch=false)
	{
		$cache = Cache::getInstance();
		
		$key = md5($call) . md5(serialize($args));
		
		if(!$touch){
			$res = $cache->get($key);
		}else{
			$res = $cache->getAndTouch($key);
		}
		
		if($res === -1 || $force){
			$res = call_user_func_array([$this,$call],$args);
			if(!empty($res) && $res !== false && $res !== 0 && !is_null($res)){
				$cache->set($key,$res,$expiry); //never set empty datas in the cache.
			}
		}
		return $res;
	}
	
	public function rmCachedCall($call,$args=[])
	{
		$key = md5($call) . md5(serialize($args));
		(Cache::getInstance())->rm($key);
	}
	
	public function updateColumn($model,$key,$value)
	{
		$model->{$key} = $value;
		$this->store($model);
	}
	
	public function updateColumnMulti($model,$key,$value,$ids)
	{
		\R::exec('UPDATE '.$model.' SET '.$key.'='.$value.' WHERE id IN ('.implode(',',$ids).')');
	}
	
	public function delete($model,$key,$value)
	{
		\R::exec('DELETE FROM '.$model.' WHERE '.$key.'=:value',[':value'=>$value]);
	}
	
	public function deleteExpired($model)
	{
		\R::exec('DELETE FROM '.$model.' WHERE time>=:value',[':value'=>time()]);
	}
	
	public function exists($model,$key,$value)
	{
		$res = \R::find($model,'WHERE '.$key.'=:value',[':value'=>$value]);
		return (bool) $res;
	}
	
	public function idBy($model,$key,$value)
	{
		return \R::getCell('SELECT id FROM '.$model.' WHERE '.$key.'=:v',[':v'=>$value]);
	}
	
	public function getCPU()
	{
		return shell_exec('mpstat | grep -A 5 "%idle" | tail -n 1 | awk -F " " \'{print 100 -  $ 12}\'a');
	}
	
	//user functions
	public function fetchUser($email)
	{
		return \R::getRow('SELECT * FROM user WHERE email=:email AND deleted="0" LIMIT 1',[':email'=>$email]);
	}
	
	public function fetchUserRestore($token)
	{
		return \R::getRow('SELECT * FROM userrestore WHERE hash=:token',[':token'=>$token]);
	}
	
	public function fetchRecoverDetails($token)
	{
		return \R::getRow('SELECT * FROM recover WHERE token=:token',[':token'=>$token]);
	}
	
	public function updateUserPassword($hash,$email)
	{
		\R::exec('UPDATE user SET hash=:hash WHERE email=:email',
			[':hash'=>$hash,':email'=>$email]
		);
	}
	// end user funcs
	
	//admin functions
	public function fetchAdmin($name)
	{
		return \R::getRow('SELECT * FROM admin WHERE name=:name LIMIT 1',[':name'=>$name]);
	}
	
	public function fetchAdminRestore($token)
	{
		return \R::getRow('SELECT * FROM adminrestore WHERE hash=:token',[':token'=>$token]);
	}
	//end admin funcs
	
	//debug funcs
	public function getTables()
	{
		return \R::inspect();
	}
	
	public function tableExists($table)
	{
		try{
			\R::inspect($table);
			return true;
		}
		catch(\exception $e){
			return false;
		}
	}
	//end debug funcs

	//application specific functions
	public function catByName($name)
	{
		return \R::getCell('SELECT id FROM category WHERE name=:name',[':name'=>$name]);
	}
	public function catById($id)
	{
		return \R::getCell('SELECT name FROM category WHERE id=:id',[':id'=>$id]);
	}
	
	public function getMasterList($type_id,$cat_id,$sort)
	{
		$sql = 'SELECT a.*,b.name AS affiliate,c.name AS type FROM masterlist a INNER JOIN admin b ON a.admin_id=b.id JOIN type c ON a.type_id=c.id';
		$params = [];
		if($type_id){
			$sql .= ' WHERE a.type_id=:tid';
			$params = array_merge($params,[':tid'=>$type_id]);
		}
		if($cat_id){
			if($type_id){
				$sql .= ' AND ';
			}else{
				$sql .= ' WHERE ';
			}
			$sql .= ' a.category_id=:cid';
			$params = array_merge($params,[':cid'=>$cat_id]);
		}
		if($sort){
			switch($sort){
				case 'ABC':
					$sql .= ' ORDER BY a.title ASC';
				break;
				case 'Available':
					if($cat_id || $type_id){
						$sql .= ' AND a.status="1"';
					}else{
						$sql .= ' WHERE a.status="1"';
					}
				break;
				case 'Pending':
					if($cat_id || $type_id){
						$sql .= ' AND a.status="2"';
					}else{
						$sql .= ' WHERE a.status="2"';
					}
				break;
				case 'Ready':
					if($cat_id || $type_id){
						$sql .= ' AND a.status="3"';
					}else{
						$sql .= ' WHERE a.status="3"';
					}
				break;
				case 'Used':
					if($cat_id || $type_id){
						$sql .= ' AND a.status="4"';
					}else{
						$sql .= ' WHERE a.status="4"';
					}
				break;
			}
		}
		if(!empty($params)){
			$data = \R::getAll($sql,$params);
		}else{
			$data = \R::getAll($sql);	
		}
		foreach($data as &$row)
		{
			$tmp = \R::getAll('SELECT name FROM country WHERE FIND_IN_SET(id,:set)',[':set'=>$row['regions']]);
			$row['regions'] = array_column($tmp,'name');
		}
		return $data;
	}
	
	public function chatLog()
	{
		return array_reverse(
			\R::getAll('SELECT a.*,b.name as username,b.avatar as avatar FROM chat a JOIN admin b ON a.admin_id=b.id ORDER BY a.id DESC LIMIT 0,100')
		);
	}
	
	public function chatUpdate($id)
	{
		$id = (int) $id;
		return array_reverse(
			\R::getAll('SELECT a.*,b.name as username,b.avatar as avatar FROM chat a JOIN admin b ON a.admin_id=b.id WHERE a.id>:id ORDER BY a.id DESC',[':id'=>$id])
		);
	}
	
	public function getPeopleProfile($id)
	{
		$data = \R::getRow('SELECT * FROM masterlist WHERE id=:id',[':id'=>$id]);
		$data['regions_list'] = \R::getAll('SELECT * FROM country WHERE id IN ('.$data['regions'].')');
		return $data;
	}
	
	public function slugs()
	{
		$data = [
			'countries'=>[],
		];
		$countries = \R::getAll('SELECT uri,id FROM country ORDER BY name ASC');
		foreach($countries as $m){
			$data['countries'][] = '/explore/'.$m['uri'];
		}
		return $data;
	}

	public function getRecent()
	{
		$data = \R::getAll('SELECT * FROM masterlist WHERE type_id IN (1,3) AND status="4" ORDER BY updated DESC LIMIT 0,12');
		foreach($data as &$row){
			$row['regions'] = \R::getAll('SELECT name,map_img,uri FROM country WHERE id IN ('.$row['regions'].')');
		}
		return $data;
	}
	
	public function  getCountry($uri)
	{
		$per_page = 20;
		$data = \R::getRow('SELECT * FROM country WHERE uri=:uri',[':uri'=>$uri]);
		$data['profiles'] = \R::getAll('SELECT a.*,b.name as category FROM masterlist a JOIN category b on b.id=a.category_id WHERE a.type_id IN(1,3) AND FIND_IN_SET(:id,a.regions) AND a.status="4" ORDER BY a.updated DESC',[':id'=>$data['id']]);
		foreach($data['profiles'] as &$row)
		{
			$row['regions'] = \R::getAll('SELECT name,map_img,uri FROM country WHERE id IN ('.$row['regions'].')');
		}
		return $data;
	}
	
	public function getMemes()
	{
		$d = \R::getAll('SELECT * FROM masterlist WHERE type_id="2" AND status="4"');
		foreach($d['profiles'] as &$row)
		{
			$row['regions'] = \R::getAll('SELECT name,map_img,uri FROM country WHERE id IN ('.$row['regions'].')');
		}
		return $d;
	}
	
	public function getCountryItem($uri)
	{
		$data = \R::getRow('SELECT * FROM masterlist WHERE uri=:uri',[':uri'=>$uri]);
		return $data;
	}
	
	public function getMenu()
	{
		$args = \R::getAll('SELECT title_banner as title, uri FROM country ORDER BY title ASC');
		foreach($args as &$row)
		{
			$row['uri'] = '/explore/'.$row['uri'];
		}
		$args[] = ['title'=>'Memes','uri'=>'/extras/memes'];
		$args[] = ['title'=>'Map','uri'=>'/map'];
		$args[] = ['title'=>'Stats','uri'=>'/stats'];
		$args[] = ['title'=>'Suggestion','uri'=>'/suggest'];
		return $args;
	}
	
	public function getAd($id,$type)
	{
		$d = \R::getRow('SELECT * FROM ad WHERE FIND_IN_SET(:id,regions) AND type=:type LIMIT 1',[':id'=>$id,':type'=>$type]);
		if(!empty($d) && $type=='image'){
			$d['images'] = json_decode($d['images'],true);
			$tmp = $d['images'];
			shuffle($tmp);
			$d['images'] = $tmp;
		}
		return (empty($d)) ? false : $d;
	}
	
	public function mapData()
	{
		$data = [];
		$d = \R::getAll('SELECT * FROM country');
		
		foreach($d as $row)
		{
			$data[]= [
				'type'=>'Feature',
				'geometry'=>[
					'type'=>'Point',
					'coordinates'=>[$row['longitude'],$row['latitude']]
				],
				'properties'=>[
					'marker-color'=>$this->randColor(),
					'title'=>$row['name'],
					'change'=>7,
					'lat'=>$row['latitude'],
					'long'=>$row['longitude'],
					'icon'=>[
						"iconSize"=>[43, 22],
						"iconAnchor"=>[50, 50],
						"popupAnchor"=>[0, -55],
						"className"=>"dot",
					],
					'capital'=>$row['capital'],
					'uri'=>$row['uri'],
					'national_dish'=>$row['national_dish'],
					'population'=>$row['population'],
					'description'=>$row['description'],
					'ethnic_data'=>$this->parseEthnic($row['ethnic_data'])
				]
			];
		}
		return $data;
	}
	
	public function searchInstant($query,$limit=true)
	{
		//if query contains multiple terms. 
		
		$original = $query;
		$like = '%'.$query.'%';
		
		$params = [
			':l'=>$like,
			':o'=>$original,
		];
		
		$sql = 'SELECT uri,img,type_id,title,regions FROM masterlist WHERE status="4" AND 
		(
		title LIKE :l OR 
		tags LIKE :l OR 
		title=:o OR
		FIND_IN_SET(:o,tags)
		';
		
		if(strpos($original,' ') !== false){
			$query = explode(' ',$query);
			if(count($query) > 5){
				//die('here');
				return []; //ddos attempt.
			}else{
				$i = 0;
				foreach($query as $q)
				{
					$sql .= ' OR title LIKE :c'.$i.' OR FIND_IN_SET(:c'.$i.',tags) ';
					$params[':c'.$i] = $q;
					$i++;
				}
			}
		}
		
		$sql .= ')'; //closing brace in giant or clause.
		
		if($limit){
			$sql.= ' LIMIT 0,12';
		}
		
		//die($sql);
		$data = \R::getAll($sql,$params);
		foreach($data as &$row){
			if($row['type_id'] !== 2){
				$r = explode(',',$row['regions']);
				$country = \R::getCell('SELECT uri FROM country WHERE id=:r',[':r'=>$r[0]]);
			}
			switch($row['type_id']){
				case 1:
				$row['uri'] = '/explore/'.$country.'/people/'.$row['uri'];
				break;
				case 3:
				$row['uri'] = '/explore/'.$country.'/fun-fact/'.$row['uri'];
				break;
				case 2: 
				$row['uri'] = '/extras/memes/'.$row['uri'];
				break;
			}
			$row['regions'] = implode(' ',$r);
		}
		return $data;
	}
	
	public function searchGraph($query)
	{
		$tmp_regions=[];
		
		$d = $this->searchInstant($query,false);
		
		$data = [
			'regions'=>[],
			'labels'=>[],
			'chart'=>[
				
				'data'=>[],
				'backgroundColor'=>[],
				'borderColor'=>[]
			],
			'results'=>$d,
		];
		
		$fails = [];

		foreach($d as &$row)
		{
			$regions = explode(' ',$row['regions']);
			foreach($regions as $r)
			{
				if(!in_array($r,$tmp_regions)){
					$tmp = \R::getRow('SELECT name,uri,id FROM country WHERE id=:id',[':id'=>$r]);
					if(!empty($tmp) && !is_null($tmp)){
						$data['labels'][$r] = $tmp['name'];
						$data['chart']['data'][$r] = 1;
						$data['chart']['backgroundColor'][$r] = $this->randColor();
						$data['chart']['borderColor'][$r] = $this->adjustColor($data['chart']['backgroundColor'][$r],15);
						$data['regions'][] = $tmp;
						$tmp_regions[] = $r;
					}
				}else{
					if(isset($data['chart']['data'][$r])){
						$data['chart']['data'][$r] += 1;
					}
				}
			}
			
		}
		foreach($data['chart'] as $k => $v)
		{
			$data['chart'][$k] = array_values($v);
		}
		$data['labels'] = array_values($data['labels']);
		
		//final step, sort the regions array so its in alphabetical order.
		$tmp = $data['regions'];
		
		$this->array_column_sort($tmp,'name',$comp = 'ASC');
		
		$data['regions'] = $tmp;
		
		return $data;
	}
	
	public function socialSettings()
	{
		$d = \R::getRow('SELECT * FROM social WHERE id="1"');
		$decode = [
			'twitter','rss','stumbleupon','facebook','google','instagram','delicious','vimeo','youtube','pinterest','flickr','lastfm','dribbble','deviantart','tumblr'
		];
		foreach($decode as $c)
		{
			$d[$c] = json_decode($d[$c],true);
		}
		return $d;
	}
	
	public function getSuggestions($email =false)
	{
		$sql = 'SELECT a.*,b.name AS type FROM suggestion a INNER JOIN type b ON a.type_id=b.id ';
		$params = [];
		if($email){
			$sql .= ' WHERE email=:email';
			$params[':email'] = $email;
		}else{
			$sql .= ' WHERE status="0"';
		}
		if(!empty($params)){
			$data = \R::getAll($sql,$params);
		}else{
			$data = \R::getAll($sql);	
		}
		foreach($data as &$row)
		{
			$tmp = \R::getAll('SELECT name FROM country WHERE FIND_IN_SET(id,:set)',[':set'=>$row['regions']]);
			$row['regions'] = array_column($tmp,'name');
		}
		return $data;
	}
	
	public function dobCountAdded()
	{
		return \R::count('masterlist',' year<>"0000"
		AND
		year IS NOT NULL
		AND
		year<>"invalid"
		AND
		day<>"00"
		AND
		day IS NOT NULL
		AND
		day<>"invalid"
		AND
		month<>"00"
		AND
		month IS NOT NULL
		AND
		month<>"invalid" AND type_id="1" ');
	}
	
	public function todayCountCalendar()
	{
		$date = explode('-',date('m-d'));
		$m = $date[0];
		$d = $date[1];
		$x = \R::count('masterlist',' day=:d AND month=:m',[':m'=>$m,':d'=>$d]);
		$y = \R::count('country',' day=:d AND month=:m',[':m'=>$m,':d'=>$d]);
		return $x + $y;
	}
	
	public function getCalendarFull($start,$end)
	{
		$x = (int)date('m',$start);
		$z = (int)date('m',$end);
		
		if($x==11 && $z==1){
			$y = 12;
			$months = [$x,$y,$z];
		}elseif($x==12 && $z==2){
			$y = 1;
			$months = [$x,$y,$z];
		}else{
			$months = range($x,$z);
		}
		$months = implode(',',$months);
		
		
		$constrain = '
		WHERE
		year<>"0000"
		AND
		year IS NOT NULL
		AND
		year<>"invalid"
		AND
		day<>"00"
		AND
		day IS NOT NULL
		AND
		day<>"invalid"
		AND
		month<>"00"
		AND
		month IS NOT NULL
		AND
		month<>"invalid"
		AND 
		month IN('.$months.')
		';
		$d1 = \R::getAll('SELECT * FROM masterlist '.$constrain.' AND type_id="1"');
		$d2 = \R::getAll('SELECT * FROM country '.$constrain);
		$d = array_merge($d1,$d2);
		
		$data = [];
		//profile birthdays and country independence dates
		foreach($d as $row)
		{
			if(isset($row['name'])){
				$t = 'inday';
				$title = 'Independence day of '.$row['name'];
				$color = '#F3FDAE';
				$url= (\StarterKit\App::getInstance())->args['base_url'].'/edit?t=country&id='.$row['id'];
			}else{
				$t = 'pbday';
				$title = 'Birthday of '.$row['title'];
				$color = '#EBBBA8';
				$url = (\StarterKit\App::getInstance())->args['base_url'].'/edit?t=profile&id='.$row['id'];
			}
			
			$date =  date('Y',$start).'-'.$row['month'].'-'.$row['day'];
			
			$data[]= [
				'title'=>$title,
				'start'=>$date,
				'end'=>$date,
				'backgroundColor'=>$color.' !important',
				'className'=>$t,
				'allDay'=>'true',
				'url'=>$url,
			];
		}
		
		//publish dates
		$d3 = \R::getAll('SELECT * FROM masterlist WHERE published REGEXP :regex',[':regex'=>'^([0-9]{4}\-{1}('.implode('|',explode(',',$months)).'){1}\-{1}[0-9]{2})$']);
		foreach($d3 as $row){
			$date =  $row['published'];
			$data[]= [
				'title'=>$row['title'].' profile published',
				'start'=>$date,
				'end'=>$date,
				'backgroundColor'=>'#3B5998 !important',
				'className'=>'pday',
				'allDay'=>'true',
				'url'=>'#',
			];
		}
		
		//affiliate birthdays
		$d4 = \R::getAll('SELECT * FROM admin '.$constrain);
		foreach($d4 as $row){
			$date =  date('Y',$start).'-'.$row['month'].'-'.$row['day'];
			$data[]= [
				'title'=>$row['name'].'\'s Birthday',
				'start'=>$date,
				'end'=>$date,
				'backgroundColor'=>'#C2DFF2 !important',
				'className'=>'afbday',
				'allDay'=>'true',
				'url'=>'#',
			];
		}
		
		$d5 = \R::getAll('SELECT * FROM customevent WHERE start>=:start AND (end<=:end OR start<=:end)',[':start'=>date('Y-m-d',$start),':end'=>date('Y-m-d',$end)]);
		foreach($d5 as $row){
			$data[]= [
				'title'=>$row['title'],
				'start'=>$row['start'],
				'end'=>$row['end'],
				'backgroundColor'=>'#BBE2AE !important',
				'className'=>'custday',
				'allDay'=>'true',
				'url'=>'#',
			];
		}
		return $data;
	}
	
	public function getCalendarMin($start,$end)
	{
		$x = (int)date('m',$start);
		$z = (int)date('m',$end);
		
		if($x==11 && $z==1){
			$y = 12;
			$months = [$x,$y,$z];
		}elseif($x==12 && $z==2){
			$y = 1;
			$months = [$x,$y,$z];
		}else{
			$months = range($x,$z);
		}
		$months = implode(',',$months);
		
		//print_r($params); die;
		
		$constrain = '
		WHERE
		year<>"0000"
		AND
		year IS NOT NULL
		AND
		year<>"invalid"
		AND
		day<>"00"
		AND
		day IS NOT NULL
		AND
		day<>"invalid"
		AND
		month<>"00"
		AND
		month IS NOT NULL
		AND
		month<>"invalid"
		AND 
		month IN('.$months.')
		';
		$d1 = \R::getAll('SELECT * FROM masterlist '.$constrain);
		$d2 = \R::getAll('SELECT * FROM country '.$constrain);
		$d = array_merge($d1,$d2);
		
		$data = [];
		foreach($d as $row)
		{
			if(isset($row['name'])){
				$t = 'inday';
				$title = 'Independence Day';
				$color = '#F3FDAE';
			}else{
				$t = 'abday';
				$title = 'Birthday';
				$color = '#C2DFF2';
			}
			
			$date = date('Y',$start).'-'.$row['month'].'-'.$row['day'];
			
			$data[]= [
				'title'=>$title,
				'start'=>$date,
				'end'=>$date,
				'backgroundColor'=>$color.' !important',
				'className'=>$t,
				'allDay'=>'true',
				'url'=>(\StarterKit\App::getInstance())->args['base_url'].'admin/calendar?month='.$row['month']
			];
		}
		return $data;
	}
	
	
	public function callPrivate($fn,$args=[])
	{
		return call_user_func_array([$this,$fn],$args);
	}
	
	public function countryPer()
	{
		$countries = \R::getAll('SELECT id,name FROM country ORDER BY name ASC');
		$data = [];
		foreach($countries as $c)
		{
			$data[] =[
				'visits'=>\R::count('masterlist',' FIND_IN_SET(:id,regions) ',[':id'=>$c['id']]),
				'country'=>$c['name'],
				'color'=>$this->randColor()
			];
		}
		return $data;
	}
	
	public function getAnalytics($k=false)
	{
		require LIB_PATH . 'analytics/GAPI.php';
		
		$cache = \StarterKit\Cache::getInstance();
		
		$key = 'admin:analytics';
		
		$data = $cache->get($key);
		
		if($data === -1){
			
			$ga = new \GoogleAnalyticsAPI('service');
			$ga->auth->setClientId('444232255351-jp55p2lohhfu4oao0bpm0lggo00sotuc.apps.googleusercontent.com'); // From the APIs console
			$ga->auth->setEmail('444232255351-jp55p2lohhfu4oao0bpm0lggo00sotuc@developer.gserviceaccount.com'); // From the APIs console
			$ga->auth->setPrivateKey( LIB_PATH . 'analytics/secret.p12');

			$auth = $ga->auth->getAccessToken();

			if ($auth['http_code'] != 200) {
				throw new \exception('Fail to connect');
			}   
			 
			$token = $auth['access_token'];
			$expires = $auth['expires_in'];
			$created = time();

			$ga->setAccessToken($token);
			$ga->setAccountId('ga:109638630');


			//set some common dates
			$now = date('Y-m-d',strtotime( date('Y-m-d') . '-1 day')); 

			// $dates represents the start date parameter for each timespan. no need to specify end date, we already have it in $now
			$dates = [
				'year'=> date("Y-m-d", strtotime('first day of January '.date('Y') )),
				'month'=> date('Y-m-01',strtotime('this month')),
				'day'=> date('Y-m-d',strtotime( $now.' -1 day'))
			];

			$data = [];

			//device 
			$data['devices'] = $ga->getVisitsBySystemOs(['max-results' => 100])['rows'];

			//visits by location
			$data['locations'] = $ga->query([
				'metrics' => 'ga:visits',
				'dimensions' => 'ga:country',
				'sort' => '-ga:visits',
				'max-results' => 50,
				'start-date' => $dates['year']
			])['rows'];

			//new vs returning
			$data['new_vs_returning'] = $ga->query([
				'metrics' => 'ga:sessions',
				'dimensions' => 'ga:userType',
			])['rows'];

			//visits by city
			$data['visits_by_city'] = $ga->query([
				'metrics' => 'ga:sessions',
				'dimensions' => 'ga:city',
				'max-results'=> 10,
			])['rows'];

			//map view
			$data['map_view'] = $ga->query([
				'metrics' => 'ga:sessions',
				'dimensions' => 'ga:city,ga:Latitude,ga:Longitude',
			])['rows'];



			//visitors by browser
			$data['browser'] = $ga->query([
				'metrics'=> 'ga:sessions',
				'dimensions' => 'ga:browser'
			])['rows'];


			//visitors by screensize
			$data['screen_sizes'] = $ga->query([
				'metrics'=> 'ga:sessions',
				'dimensions' => 'ga:screenResolution',
			])['rows'];


			//visits by service provider
			$data['isp'] = $ga->query([
				'metrics'=> 'ga:sessions',
				'dimensions' => 'ga:networkDomain',
				'max-results' => 50
			])['rows'];

			//most popular pages
			$data['pages'] = $ga->query([
				'metrics'=> 'ga:pageViews',
				'dimensions' => 'ga:pagePath',
				'max-results'=>10
			])['rows'];

			$data['hits_by_day'] = $ga->query([
				'metrics'=> 'ga:pageviews,ga:visitors',
				'dimensions' => 'ga:date',
				'start-date' => $dates['month'],
				'end-date'=> $now
			])['rows'];
			
			$data['hits_by_country'] = $ga->query([
				'metrics'=> 'ga:visitors',
				'dimensions' => 'ga:country',
				'start-date' => $dates['month'],
				'end-date'=> $now
			])['rows'];
			
			$data['hits_by_city'] = $ga->query([
				'metrics'=> 'ga:visitors',
				'dimensions' => 'ga:city',
				'start-date' => $dates['month'],
				'end-date'=> $now
			])['rows'];

			$data['this_month'] = [
				'name'=>date('M'),
				'data'=>$ga->query([
					'metrics' => 'ga:visits',
					'dimensions' => 'ga:date',
					'start-date' => $dates['month'],
					'end-date'=> $now
				])['rows']
			];

			$data['this_year'] = [
				'name'=>date('Y'),
				'data'=>$ga->query([
					'metrics' => 'ga:visits',
					'dimensions' => 'ga:date',
					'start-date' => $dates['year'],
					'end-date'=> $now
				])['rows']
			];
			
			$data['months_in_year'] = $ga->query([
				'metrics' => 'ga:pageviews',
				'dimensions' => 'ga:month',
				'sort' => 'ga:month',
				'start-date' => $dates['year'],
				'end-date'=> $now
			])['rows'];
			
			$data['social'] = $ga->query([
				'metrics' => 'ga:socialActivities',
				'dimensions' => 'ga:week',
				'sort' => 'ga:week',
				'start-date' => '30daysAgo',
				'end-date'=> 'yesterday',
				'max-results'=>25,
			])['rows'];
			
			$data['referrals'] = $ga->query([
				'metrics' => 'ga:users',
				'dimensions' => 'ga:referralPath',
				'sort' => 'ga:referralPath',
				'start-date' => '30daysAgo',
				'end-date'=> 'yesterday',
				'max-results'=>20
			])['rows'];
			
			$data['device_type'] = $ga->query([
				'metrics' => 'ga:sessions',
				'dimensions' => 'ga:deviceCategory',
				'sort' => 'ga:deviceCategory',
				'start-date' => '30daysAgo',
				'end-date'=> 'yesterday',
				'max-results'=>20
			])['rows'];
			
			$data['mobile_devices'] = $ga->query([
				'metrics' => 'ga:sessions',
				'dimensions' => 'ga:mobileDeviceInfo',
				'sort' => 'ga:mobileDeviceInfo',
				'start-date' => '30daysAgo',
				'end-date'=> 'yesterday',
				'max-results'=>20
			])['rows'];
			
			try{
				$data['search_terms'] = $ga->query([
					'metrics' => 'ga:searchResultViews',
					'dimensions' => 'ga:searchKeyword',
					'sort' => 'ga:searchKeyword',
					'start-date' => '30daysAgo',
					'end-date'=> 'yesterday',
					'max-results'=>50
				])['rows'];	
			}
			catch(\exception $e){
				$data['search_terms'] = [['None Yet',0]];
			}
			
			$data['last_seven'] = $ga->query([
				'metrics' => 'ga:pageviews',
				'dimensions' => 'ga:dayOfWeek',
				'sort' => 'ga:dayOfWeek',
				'start-date' => '8daysAgo',
				'end-date'=> 'yesterday',
				'max-results'=>50
			])['rows'];
			
			if(!empty($data) && $data !== false && $data !== 0 && !is_null($data)){
				$cache->set($key,$data,60*60); //never set empty arrays to cache cache!
			}
		}
		if($k){
			if(isset($data[$k])){
				return $data[$k];
			}
		}
		return $data;
	}
	
	public function getCountPerCountryByCategoryId($id)
	{
		$c = \R::getAll('SELECT regions FROM masterlist WHERE category_id=:id',[':id'=>$id]);
		$data = [];
		foreach($c as $r)
		{
			$tmp = explode(',',$r['regions']);
			foreach($tmp as $t)
			{
				$t = (int) trim($t);
				if(!isset($data[$t])){
					$data[$t] = [
						'country'=>$this->getCell('SELECT name FROM country WHERE id=:id',[':id'=>$t]),
						'count'=>1
					];
				}else{
					$data[$t]['count'] += 1;
				}
			}
		}
		return array_values($data);
	}
	
	public function countProfilesPerCountry()
	{
		$data = \R::getAll('SELECT id,name FROM country');
		foreach($data as &$row){
			$row['count'] = \R::count('masterlist', ' FIND_IN_SET(:id,regions) ',[':id'=>$row['id']]);
		}
		return $data;
	}
	
	public function countPublishedByTypeCountry($type_id)
	{
		$data = \R::getAll('SELECT id,name FROM country');
		foreach($data as &$row){
			$row['count'] = \R::count('masterlist', 'status="4" AND type_id=:tid AND FIND_IN_SET(:id,regions) ',[':tid'=>$type_id,':id'=>$row['id']]);
		}
		return $data;
	}
	
	public function countProfilesByStatus()
	{
		$data = \R::getAll('select count(id) as num,`status` as `name` FROM masterlist GROUP BY `status`');
		foreach($data as &$row){
			switch($row['name']){
				case 1:
					$status = 'Available';
				break;
				case 2:
					$status = 'Pending';
				break;
				case 3:
					$status = 'Ready';
				break;
				case 4:
					$status = 'Used';
				break;
				default:
				case null:
					$status = 'Not Set';
				break;
			}
			$row['name'] = $status;
		}
		return $data;
	}
	
	public function getNfeed($admin_id)
	{
		return \R::getAll('SELECT * FROM notification WHERE admin_id IS NULL OR admin_id=:id ORDER BY id DESC',[':id'=>$admin_id]);
	}
	public function countProfilesByType()
	{
		return \R::getAll('SELECT count(a.id) AS num,b.name FROM masterlist a JOIN type b on a.type_id=b.id GROUP BY b.name');
	}
	
	public function countProfilesByAffiliate()
	{
		return \R::getAll('SELECT count(a.id) AS num,b.name FROM masterlist a JOIN admin b on a.admin_id=b.id GROUP BY b.name');
	}
	
	public function countSuggestionsByType()
	{
		return \R::getAll('SELECT count(a.id) AS num,b.name FROM suggestion a JOIN type b on a.type_id=b.id GROUP BY b.name');
	}
	
	public function countSuggestionsPerCountry()
	{
		$data = \R::getAll('SELECT id,name FROM country');
		foreach($data as &$row){
			$row['count'] = \R::count('suggestion', ' FIND_IN_SET(:id,regions) ',[':id'=>$row['id']]);
		}
		return $data;
	}
	
	public function birthdaysByMonth()
	{
		return \R::getAll('SELECT count(id) as num, MONTHNAME(STR_TO_DATE(month, "%m")) as month FROM masterlist WHERE type_id="1" and month<>"NULL" and month<>"00" and month<>"invalid" GROUP BY month');
	}
	
	//end app specific funcs
	
	//private utilities
	private function paginatedQuery($sql,$params,$page = 1, $per_page=12)
	{
		//we know that php is 0 based, but for presentation purposes pagination should be 1 based.
		//citing this knowledge, we always need to subtract `1` from $page;
		$page = (int) $page;
		if($page < 1){
			$page = 1;
		}
		$offset = abs($per_page * ($page - 1)); // page - 1 * per_page = offset
		$sql.=' LIMIT '.$offset.','.$per_page;
		if(empty($params)){
			$data = \R::getAll($sql);
		}else{
			$data = \R::getAll($sql,$params);
		}
		return $data;
	}
	
	private function url_safe($title)
	{
		$title = preg_replace('/[^A-Za-z 0-9]/','',$title);
		$title = preg_replace('/[\t\n\r\0\x0B]/', '', $title);
		$title = preg_replace('/([\s])\1+/', ' ', $title);
		$title = trim($title);
		$title = str_replace(' ','-',$title);
		return $title;
	}
	
	private function array_column_sort(&$array, $key,$comp = 'DESC')
	{
		if($comp == 'DESC'){
			usort($array, function($a, $b) use ($key){ return $a[$key] == $b[$key]? 0 : $a[$key] < $b[$key] ? 1 : -1;});
		}else{
			usort($array, function($a, $b) use ($key){ return $a[$key] == $b[$key]? 0 : $a[$key] > $b[$key] ? 1 : -1;});
		}
	}
	
	private function array_column_merge(&$array,$source,$column)
	{
		$i = 0;
		foreach($array as &$row)
		{
			$row[$column] = isset($source[$i]) ? $source[$i] : null;
			$i++;
		}
	}
	
	private function randColor()
	{
		$c = '#' . substr(str_shuffle(implode(array_merge(range(0, 9), range('A', 'F')))), 0, 6);
		return $c;
	}
	
	private function adjustColor($hex, $steps) {
		// Steps should be between -255 and 255. Negative = darker, positive = lighter
		$steps = max(-255, min(255, $steps));

		// Normalize into a six character long hex string
		$hex = str_replace('#', '', $hex);
		if (strlen($hex) == 3) {
			$hex = str_repeat(substr($hex,0,1), 2).str_repeat(substr($hex,1,1), 2).str_repeat(substr($hex,2,1), 2);
		}

		// Split into three parts: R, G and B
		$color_parts = str_split($hex, 2);
		$return = '#';

		foreach ($color_parts as $color) {
			$color   = hexdec($color); // Convert to decimal
			$color   = max(0,min(255,$color + $steps)); // Adjust color
			$return .= str_pad(dechex($color), 2, '0', STR_PAD_LEFT); // Make two char hex code
		}

		return $return;
	}
	
	private function parseEthnic($data)
	{
		$data = str_replace(['Ethnic Groups','%'],'',$data);
		$data = explode(PHP_EOL,$data);
		foreach($data as &$d)
		{
			$tmp = explode(' ',$d,2);
			if(count($tmp) !== 2){
				$d = '';
			}else{
				$tmp2 = [
					'value'=>$tmp[0],
					'label'=>$tmp[1],
					'color'=>$this->randColor()
				];
				
				$tmp2['highlight']=$this->adjustColor($tmp2['color'],-15);
				$d = $tmp2;	
			}
		}
		return array_filter($data);
	}
	
	private function countFormat($num)
	{
		if($num < 1000){
			$formatted = $num;
		}
		elseif($num >= 1000 && $num < 1000000){
			if( $num % 1000 === 0 ){
				$formatted = ($num/1000);
			}else{
				$formatted = substr($num, 0, -3).'.'.substr($num, -3, -2);
				if(substr($formatted, -1, 1) === '0')
				{
					$formatted = substr($formatted, 0, -2);
				}
			}

			$formatted.= 'K';
		}
		elseif($num > 1000000 && $num < 1000000000){
			if( $num % 1000000 === 0 ){
				$formatted = ($num/1000000);
			}else{
				$formatted = substr($num, 0, -6).'.'.substr($num, -6, -2);
				if(substr($formatted, -1, 1) === '0')
				{
					$formatted = substr($formatted, 0, -2);
				}
			}

			$formatted.= 'M';
		}
		elseif($num > 1000000000){
			if( $num % 1000000000 === 0 ){
				$formatted = ($num/1000000000);
			}else{
				$formatted = substr($num, 0, -9).'.'.substr($num, -9, -2);
				if(substr($formatted, -1, 1) === '0')
				{
					$formatted = substr($formatted, 0, -2);
				}
			}

			$formatted.= 'B';
		}
		return $formatted;
	}
	//end private utilities
}