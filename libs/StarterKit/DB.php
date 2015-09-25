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
			call_user_func_array(['\R',$method],$args);
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
				$cache->set($key,$res,$expiry); //never set empty arrays to cache cache!
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
	
	//admin functions
	public function fetchAdmin($email)
	{
		return \R::getRow('SELECT * FROM admin WHERE email=:email LIMIT 1',[':email'=>$email]);
	}
	
	public function fetchAdminRestore($token)
	{
		return \R::getRow('SELECT * FROM adminrestore WHERE hash=:token',[':token'=>$token]);
	}
	
	
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

	//application specific functions
	
	public function getFeatured($type,$page = 1)
	{
		$per_page = 16;
		switch($type){
			case 'singles':
				$s = $this->paginatedQuery('SELECT 
				b.*,
				c.name AS artist
				FROM featuredsingle a 
				INNER JOIN track b ON a.track_id=b.id
				INNER JOIN artist c ON b.artist_id = c.id 
				WHERE b.approved="1" AND b.deleted="0"
				ORDER BY a.id DESC',
				[],
				$page,
				$per_page
				);
				foreach($s as &$track){
					$track['likes'] = \R::count('likes',' track_id=:id',[':id'=>$track['id']]) - 1;
					$track['views'] = \R::count('views',' track_id=:id',[':id'=>$track['id']]) - 1;
				}
				return $s;
			break;
			case 'mixtapes':
				$s = $this->paginatedQuery('SELECT 
				b.*,
				c.name as artist
				FROM featuredmixtape a 
				INNER JOIN mixtape b ON a.mixtape_id=b.id 
				INNER JOIN artist c ON b.artist_id = c.id 
				WHERE b.approved="1" and b.deleted="0"
				ORDER BY a.id DESC',
				[],
				$page,
				$per_page
				);
				foreach($s as &$mixtape){
					$mixtape['likes'] = \R::count('likes',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
					$mixtape['views'] = \R::count('views',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
				}
				return $s;	
			break;
			default:
				throw new \exception('Invalid type');
		}
	}
	
	public function getTrack($id)
	{
		$track = \R::getRow('SELECT a.*,b.user_name as user,c.name AS artist_name,c.uri AS artist_uri
		FROM track a 
		INNER JOIN user b ON b.id = a.user_id 
		INNER JOIN artist c on c.id = a.artist_id 
		WHERE a.deleted="0" AND a.approved="1" AND a.is_single="1" AND a.id=:id',
			[':id'=>$id]
		);
		if(empty($track)){
			return [];
		}
		$track['likes'] = \R::count('likes',' track_id=:id',[':id'=>$id]) - 1;
		$track['views'] = \R::count('views',' track_id=:id',[':id'=>$id]) - 1;
		$track['featuring'] = $this->explFeaturing($track['featuring']);
		$track['audio_file'] = array_pop(explode('/',$track['audio_file']));
		return $track;
	}
	
	public function getMixtape($id)
	{
		$mixtape = \R::getRow('SELECT a.*,b.user_name as user,c.name AS artist_name,c.uri AS artist_uri
		FROM mixtape a 
		INNER JOIN user b ON b.id = a.user_id 
		INNER JOIN artist c ON c.id = a.artist_id 
		
		WHERE a.id=:id AND a.approved="1"',[':id'=>$id]);
		if(empty($mixtape)){
			return [];
		}
		$mixtape['tracks'] = \R::getAll('SELECT a.*,b.user_name as user,c.name AS artist_name,c.uri AS artist_uri
		FROM track a 
		INNER JOIN user b ON b.id = a.user_id 
		INNER JOIN artist c on c.id = a.artist_id 
		WHERE a.deleted="0" AND a.is_single="0" AND a.mixtape_id=:id',[':id'=>$id]);
		if(empty($mixtape['tracks'])){
			throw new \exception('No tracks found for mixtape');
		}
		foreach($mixtape['tracks'] as &$track){
			$track['audio_file'] = array_pop(explode('/',$track['audio_file']));
		}
		$mixtape['likes'] = \R::count('likes',' mixtape_id=:id',[':id'=>$id]) - 1;
		$mixtape['views'] = \R::count('views',' mixtape_id=:id',[':id'=>$id]) - 1;
		$mixtape['featuring'] = $this->explFeaturing($mixtape['featuring']);
		return $mixtape;
	}
	
	public function getArtists($page = 1)
	{
		$per_page = 6;
		return $this->paginatedQuery('SELECT * FROM artist ORDER BY name ASC',[],$page,$per_page);
	}
	
	public function getMixtapes($page = 1,$sort = false,$timespan = false)
	{
		//todo handle sort args and time_span limits
		$per_page = 12;
		switch($timespan){
			case false:
			case 'this_week':
				$time1 = strtotime(date("o-\WW"));
				$time2 = strtotime("+6 days 23:59:59", $time1);
			break;
			case 'today':
				$time1 = strtotime("midnight", time());
				$time2   = strtotime("tomorrow", $time1) - 1;
			break;
			case 'this_month':
				$time1 = mktime(0, 0, 0, date("n"), 1);
				$time2 = mktime(23, 59, 0, date("n"), date("t"));
			break;
			case 'all_time':
				$time1 = 0;
				$time2 = time();
			break;
			default:
				throw new \exception('Invalid timespan');
			break;
		}
		switch($sort){
			case false:
			case 'popular':
				$most_viewed = $this->paginatedQuery(
					'SELECT mixtape_id,COUNT(1) as `views` 
					FROM views 
					WHERE (timestamp BETWEEN :time1 AND :time2) AND mixtape_id IS NOT NULL
					GROUP BY mixtape_id ORDER BY `views` DESC',
					['time1'=>$time1,'time2'=>$time2],
					$page,
					$per_page
				);
				$most_liked = $this->paginatedQuery(
					'SELECT mixtape_id,COUNT(1) as `likes` 
					FROM likes 
					WHERE (timestamp BETWEEN :time1 AND :time2) AND mixtape_id IS NOT NULL
					GROUP BY mixtape_id ORDER BY `likes` DESC',
					['time1'=>$time1,'time2'=>$time2],
					$page,
					$per_page
				);
				if(empty($most_liked) && empty($most_viewed)){
					return [];
				}
				$ids = array_unique(
					array_column(
						array_merge($most_viewed,$most_liked),
						'mixtape_id'
					)
				);
				$data = \R::getAll('SELECT a.*,b.name AS artist_name,b.uri AS artist_uri 
				FROM mixtape a INNER JOIN artist b ON b.id = a.artist_id 
				WHERE a.deleted="0" AND a.approved="1" AND a.id IN('.implode(',',$ids).')
				ORDER BY FIELD(a.id, '.implode(',',$ids).')
				');
			break;
			case 'latest':
				$data = $this->paginatedQuery(
					'SELECT a.*,b.name AS artist_name,b.uri AS artist_uri FROM mixtape a 
					INNER JOIN artist b ON b.id = a.artist_id 
					WHERE a.deleted="0" AND a.approved="1" 
					ORDER BY a.added DESC',
					[],
					$page,
					$per_page
				);
			break;
			default:
				throw new \exception('invalid sort');
			break;
		}
		foreach($data as &$mixtape){
			$mixtape['likes'] = \R::count('likes',' mixtape_id=:id ',[':id'=>$mixtape['id']]) - 1;
			$mixtape['views'] = \R::count('views',' mixtape_id=:id ',[':id'=>$mixtape['id']]) - 1;
			$mixtape['featuring'] = $this->explFeaturing($mixtape['featuring']);
		}
		return $data;
	}
	
	public function getSingles($page = 1,$sort = false,$timespan = false)
	{
		//todo handle sort args and time_span limits
		$per_page = 12;
		switch($timespan){
			case false:
			case 'this_week':
				$time1 = strtotime(date("o-\WW"));
				$time2 = strtotime("+6 days 23:59:59", $time1);
			break;
			case 'today':
				$time1 = strtotime("midnight", time());
				$time2   = strtotime("tomorrow", $time1) - 1;
			break;
			case 'this_month':
				$time1 = mktime(0, 0, 0, date("n"), 1);
				$time2 = mktime(23, 59, 0, date("n"), date("t"));
			break;
			case 'all_time':
				$time1 = 0;
				$time2 = time();
			break;
			default:
				throw new \exception('Invalid timespan');
			break;
		}
		switch($sort){
			case false:
			case 'popular':
				$most_viewed = $this->paginatedQuery(
					'SELECT track_id,COUNT(1) as `views` 
					FROM views 
					WHERE (timestamp BETWEEN :time1 AND :time2) AND track_id IS NOT NULL
					GROUP BY track_id ORDER BY `views` DESC',
					['time1'=>$time1,'time2'=>$time2],
					$page,
					$per_page
				);
				$most_liked = $this->paginatedQuery(
					'SELECT mixtape_id,COUNT(1) as `likes` 
					FROM likes 
					WHERE (timestamp BETWEEN :time1 AND :time2) AND track_id IS NOT NULL
					GROUP BY track_id ORDER BY `likes` DESC',
					['time1'=>$time1,'time2'=>$time2],
					$page,
					$per_page
				);
				if(empty($most_liked) && empty($most_viewed)){
					return [];
				}
				$ids = array_unique(
					array_column(
						array_merge($most_viewed,$most_liked),
						'track_id'
					)
				);
				$data = \R::getAll('SELECT a.*,b.name AS artist_name, b.uri AS artist_uri 
				FROM track a INNER JOIN artist b ON b.id = a.artist_id 
				WHERE a.deleted="0" AND a.approved="1"
				AND a.id IN('.implode(',',$ids).')
				ORDER BY FIELD(a.id, '.implode(',',$ids).')
				');
			break;
			case 'latest':
				$data = $this->paginatedQuery(
					'SELECT a.*,b.name AS artist_name,b.uri AS artist_uri FROM track a 
					INNER JOIN artist b ON b.id = a.artist_id 
					WHERE a.deleted="0" AND a.approved="1" AND a.is_single="1" ORDER BY a.added DESC ',
					[],
					$page,
					$per_page
				);
			break;
			default:
				throw new \exception('invalid sort');
			break;
		}
		foreach($data as &$track){
			$track['likes'] = \R::count('likes',' track_id=:id ',[':id'=>$track['id']]) - 1;
			$track['views'] = \R::count('views',' track_id=:id ',[':id'=>$track['id']]) - 1;
			$track['featuring'] = $this->explFeaturing($track['featuring']);
		}
		return $data;
	}
	
	public function artistProfile($name)
	{
		$artist = $this->artistByURI($name);
		if(!empty($artist)){
			$artist['singles'] = $this->artistContent($artist['name'],'single');
			$artist['mixtapes'] = $this->artistContent($artist['name'],'mixtape');
		}
		return $artist;
	}
	
	public function artistByURI($name)
	{
		return \R::getRow('SELECT * FROM artist WHERE uri=:name ',[':name'=>$name]);
	}
	
	public function artistByName($name)
	{
		return \R::getRow('SELECT * FROM artist WHERE (name=:name OR name LIKE :p2) ',[':name'=>$name,':p2'=>'%'.$name.'%']);
	}
	
	public function artistByNameAdv($name)
	{
		$artist = $this->artistByName($name);
		if(empty($artist)){
			$name = trim($name);
			$segments = explode(' ',$name);
			if(count($segments) > 1){
				foreach($segments as $segment){
					$artist = $this->artistByName($segment);
					if(!empty($artist)){
						break;
					}
				}
			}
		}
		return $artist;
	}
	
	public function artistContent($artist,$type,$page = 1)
	{
		$per_page = 6;
		switch($type){
			case 'mixtape':
				$d = $this->paginatedQuery(
					'SELECT a.*,b.name AS artist_name,b.uri AS artist_uri FROM mixtape a 
					INNER JOIN artist b ON b.id=a.artist_id 
					WHERE (b.name=:n OR a.featuring LIKE :l) AND a.approved="1" ORDER BY a.id DESC',
					[':n'=>$artist,':l'=>'%'.$artist.'%'],
					$page,
					$per_page
				);
				foreach($d as &$mixtape){
					$mixtape['likes'] = \R::count('likes',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
					$mixtape['views'] = \R::count('views',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
					$mixtape['featuring'] = $this->explFeaturing($mixtape['featuring']);
				}
			break;
			case 'single':
				$d = $this->paginatedQuery(
					'SELECT a.*,b.name AS artist_name,b.uri AS artist_uri FROM track a 
					INNER JOIN artist b ON b.id=a.artist_id 
					WHERE (b.name=:n OR a.featuring LIKE :l) AND a.is_single="1" AND a.approved="1" ORDER BY a.id DESC',
					[':n'=>$artist,':l'=>'%'.$artist.'%'],
					$page,
					$per_page
				);
				foreach($d as &$single){
					$single['likes'] = \R::count('likes',' track_id=:id',[':id'=>$single['id']]) - 1;
					$single['views'] = \R::count('views',' track_id=:id',[':id'=>$single['id']]) - 1;
					$single['featuring'] = $this->explFeaturing($single['featuring']);
				}
			break;
		}
		return $d;
	}
	
	public function contentSearch($query,$type,$page = 1)
	{
		$per_page = 6;
		switch($type){
			case 'mixtape':
				$d = $this->paginatedQuery(
					'SELECT a.*,b.name AS artist_name,b.uri AS artist_uri FROM mixtape a 
					INNER JOIN artist b ON b.id=a.artist_id 
					WHERE (a.title LIKE :m OR b.name LIKE :n OR a.featuring LIKE :l) AND a.approved="1" ORDER BY a.id DESC',
					[':m'=>'%'.$query.'%',':n'=>'%'.$query.'%',':l'=>'%'.$query.'%'],
					$page,
					$per_page
				);
				foreach($d as &$mixtape){
					$mixtape['likes'] = \R::count('likes',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
					$mixtape['views'] = \R::count('views',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
					$mixtape['featuring'] = $this->explFeaturing($mixtape['featuring']);
				}
			break;
			case 'single':
				$d = $this->paginatedQuery(
					'SELECT a.*,b.name AS artist_name,b.uri AS artist_uri FROM track a 
					INNER JOIN artist b ON b.id=a.artist_id 
					WHERE (a.title LIKE :m OR b.name LIKE :n OR a.featuring LIKE :l) AND a.is_single="1" AND a.approved="1" ORDER BY a.id DESC',
					[':m'=>'%'.$query.'%',':n'=>'%'.$query.'%',':l'=>'%'.$query.'%'],
					$page,
					$per_page
				);
				foreach($d as &$single){
					$single['likes'] = \R::count('likes',' track_id=:id',[':id'=>$single['id']]) - 1;
					$single['views'] = \R::count('views',' track_id=:id',[':id'=>$single['id']]) - 1;
					$single['featuring'] = $this->explFeaturing($single['featuring']);
				}
			break;
		}
		return $d;
	}
	
	public function search($q,$page=1)
	{
		$q = urldecode($q);
		$results = [];
		$results['artist'] = $this->artistByNameAdv($q);
		if(!empty($results['artist'])){
			//we return tracks and mixtapes featuring that artist.
			$results['singles'] = $this->artistContent($results['artist']['name'],'single',$page);
			$results['mixtapes'] = $this->artistContent($results['artist']['name'],'mixtape',$page);
		}else{
			$results['singles'] = $this->contentSearch($q,'single',$page);
			$results['mixtapes'] = $this->contentSearch($q,'mixtape',$page);
		}
		return $results;
	}
	
	public function searchPaginate($q,$type,$page)
	{
		switch($type)
		{
			case 'mixtapes':
				$artist = $this->artistByName($q);
				if(!empty($artist)){
					$data = $this->artistContent($q,'mixtape');
				}else{
					$data = [];
				}
			break;
			case 'singles':
				$artist = $this->artistByName($q);
				if(!empty($artist)){
					$data = $this->artistContent($q,'single');
				}else{
					$data = [];
				}
			break;
		}
		return $data;
	}
	
	public function getRelated($type,$id,$mobile=false)
	{
		$cache = Cache::getInstance();
		$key = '_r_'.md5($type.$id.$mobile);
		$related = $cache->get($key);
		if($related == -1){
			$related = $this->fakeRelated($type,$id,$mobile); //no cache data exists. just fake it
			$cache->set($key,$related);
		}
		return $related;
	}
	
	public function fakeRelated($type,$id,$mobile=false)
	{
		$targ = 5;
		if($mobile){
			$targ++; //show six results for mobile so they stack evenly
		}
		//type is a dummy value for disambiguation purposes to the cache driver
		$mixtapes = \R::getAll('SELECT a.*,b.name AS artist FROM mixtape a JOIN artist b ON b.id=a.artist_id WHERE a.approved="1" AND a.id!=:id ORDER BY RAND() LIMIT 0,10',
			[':id'=>$id]
		);
		$tracks   = \R::getAll('SELECT a.*,b.name AS artist FROM track a JOIN artist b ON b.id=a.artist_id WHERE a.approved="1" AND a.id!=:id ORDER BY RAND() LIMIT 0,10',
			[':id'=>$id]
		);
		$merged = array_merge($mixtapes,$tracks);
		shuffle($merged);
		$num = count($merged);
		if($num > $targ){
			$max = $num - $targ;
			$offset = rand(0,$max);
			$done = array_slice($merged,$offset,$targ);
		}else{
			$done = $merged;
		}
		return $done;
	}
	
	public function getDownloadableMixtape($id)
	{
		$mixtape = \R::getRow('SELECT a.*,b.user_name as user,c.name AS artist_name,c.uri AS artist_uri 
		FROM mixtape a 
		INNER JOIN user b ON b.id = a.user_id 
		INNER JOIN artist c on c.id = a.artist_id 
		WHERE a.id=:id AND a.approved="1" AND a.download="1"',[':id'=>$id]);
		if(empty($mixtape)){
			return false;
		}
		$mixtape['tracks'] = \R::getAll('SELECT a.*,b.user_name as user,c.name AS artist_name,c.uri AS artist_uri
		FROM track a 
		INNER JOIN user b ON b.id = a.user_id 
		INNER JOIN artist c on c.id = a.artist_id 
		WHERE a.deleted="0" AND a.is_single="0" AND a.mixtape_id=:id',[':id'=>$id]);
		if(empty($mixtape['tracks'])){
			die('problems with file download');
		}
		foreach($mixtape['tracks'] as &$track){
			$track['featuring']  = $this->explFeaturing($track['featuring']);
			$track['audio_file'] = array_pop(explode('/',$track['audio_file']));
		}
		return $mixtape;
	}
	
	public function getDownloadableSingle($id)
	{
		$track = \R::getRow('SELECT a.*,b.name AS artist_name,b.uri as artist_uri FROM track a INNER JOIN artist b ON b.id = a.artist_id WHERE a.id=:id AND a.download="1" AND a.approved="1"',[':id'=>$id]);
		$track['audio_file'] = array_pop(explode('/',$track['audio_file']));
		return $track;
	}
	
	public function flagUserComments($uid)
	{
		\R::exec('UPDATE comment SET flagged="1" WHERE timestamp>=:time AND user_id=:id',[':id'=>$uid,':time'=>time() - (5 * 60)]);
	}
	
	public function getComments($type,$id,$page=1)
	{
		$per_page = 24;
		switch($type){
			case 'mixtape':
				$data = $this->paginatedQuery(
					'SELECT a.timestamp,a.comment,b.small AS user_avatar,b.user_name as user_name 
					FROM comment a 
					INNER JOIN user b ON b.id = a.user_id 
					WHERE a.mixtape_id=:id AND a.deleted="0" AND a.flagged="0" ORDER BY a.id DESC',
					[':id'=>$id],
					$page,
					$per_page
				);
			break;
			case 'single':
				$data = $this->paginatedQuery(
					'SELECT a.timestamp,a.comment,b.small AS user_avatar,b.user_name as user_name 
					FROM comment a 
					INNER JOIN user b ON b.id = a.user_id 
					WHERE a.track_id=:id and a.deleted="0" AND a.flagged="0" ORDER BY a.id DESC',
					[':id'=>$id],
					$page,
					$per_page
				);
			break;
			default:
				throw new \exception('bad request');
			break;
		}
		return $data;
	}
	
	public function userHasLiked($type,$id,$user_id)
	{
		switch($type){
			case 'mixtape':
				return (bool) !empty(\R::getRow('SELECT id FROM likes WHERE mixtape_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$user_id]));
			break;
			case 'single':
				return (bool) !empty(\R::getRow('SELECT id FROM likes WHERE track_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$user_id]));
			break;
			default:
				throw new \exception('bad request');
			break;
		}
	}
	
	public function userHasFavorited($type,$id,$user_id)
	{
		switch($type){
			case 'mixtape':
				return (bool) !empty(\R::getRow('SELECT id FROM favorites WHERE mixtape_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$user_id]));
			break;
			case 'single':
				return (bool) !empty(\R::getRow('SELECT id FROM favorites WHERE track_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$user_id]));
			break;
			default:
				throw new \exception('bad request');
			break;
		}
	}
	
	public function removeLike($type,$id,$uid)
	{
		switch($type){
			case 'mixtape':
				\R::exec('DELETE FROM likes WHERE mixtape_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$uid]);
			break;
			case 'single':
				\R::exec('DELETE FROM likes WHERE track_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$uid]);
			break;
			default:
				throw new \exception('bad request');
			break;
		}
	}
	
	public function removeFavorite($type,$id,$uid)
	{
		switch($type){
			case 'mixtape':
				\R::exec('DELETE FROM favorites WHERE mixtape_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$uid]);
			break;
			case 'single':
				\R::exec('DELETE FROM favorites WHERE track_id=:id AND user_id=:uid',[':id'=>$id,':uid'=>$uid]);
			break;
			default:
				throw new \exception('bad request');
			break;
		}
	}
	
	public function getUserLikes($uid)
	{
		return \R::getAll('SELECT mixtape_id,track_id FROM likes WHERE user_id=:uid',[':uid'=>$uid]);
	}
	
	public function getUserFavorites($uid)
	{
		return \R::getAll('SELECT mixtape_id,track_id FROM favorites WHERE user_id=:uid',[':uid'=>$uid]);
	}
	
	public function getUserFavoritesFull($type,$user,$page = 1)
	{
		$per_page = 12;
		$limit = abs($per_page * (intval($page) - 1));
		switch($type){
			case 'mixtapes':
				@$mixtape_ids = array_slice($user->favorites['mixtape'],$limit,$per_page);
				if(empty($mixtape_ids)){
					return [];
				}
				$mixtapes = \R::getAll('SELECT a.*,b.name AS artist_name FROM mixtape a INNER JOIN artist b ON b.id = a.artist_id WHERE a.deleted="0" AND a.approved="1" AND a.id IN('.implode(',',$mixtape_ids).')');
					
				foreach($mixtapes as &$mixtape){
					$mixtape['likes'] = \R::count('likes',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
					$mixtape['views'] = \R::count('views',' mixtape_id=:id',[':id'=>$mixtape['id']]) - 1;
					$mixtape['featuring'] = $this->explFeaturing($mixtape['featuring']);
				}	
				return $mixtapes;
			break;
			case 'singles':
				@$single_ids  = array_slice($user->favorites['single'],$limit,$per_page);
				if(empty($single_ids)){
					return [];
				}
				$singles = \R::getAll('SELECT a.*,b.name AS artist_name FROM track a INNER JOIN artist b ON b.id = a.artist_id WHERE a.deleted="0" AND a.approved="1" AND a.id IN('.implode(',',$single_ids).')');
				foreach($singles as &$single){
					$single['likes'] = \R::count('likes',' track_id=:id',[':id'=>$single['id']]) - 1;
					$single['views'] = \R::count('views',' track_id=:id',[':id'=>$single['id']]) - 1;
					$single['featuring'] = $this->explFeaturing($single['featuring']);
				}
				return $singles;
			break;
			default:
				return [];
		}
	}
	
	public function viewIncr($type,$id,$uniq)
	{
		switch($type){
			case 'mixtape':
				if(!$this->exists('mixtape','id',$id)){
					throw new \exception('');
				}
				$exist = \R::getRow('SELECT * FROM views WHERE timestamp >:t AND mixtape_id != NULL AND mixtape_id=:id AND uniq=:u',
					[':t'=>time() - (60 * 60 * 2),':id'=>$id,':u'=>$uniq]
				);
				if(empty($exist)){
					$t = $this->model('views');
					$t->mixtape_id = (int)$id;
					$t->uniq = $uniq;
					$t->timestamp = time();
					$this->store($t);
				}
			break;
			case 'single':
				if(!$this->exists('track','id',$id)){
					throw new \exception('');
				}
				$exist = \R::getRow('SELECT * FROM views WHERE timestamp >:t AND track_id != NULL AND track_id=:id AND uniq=:u',
					[':t'=>time() - (60 * 60 * 2),':id'=>$id,':u'=>$uniq]
				);
				if(empty($exist)){
					$t = $this->model('views');
					$t->track_id = (int)$id;
					$t->uniq = $uniq;
					$t->timestamp = time();
					$this->store($t);
				}
			break;
			default:
				throw new \exception('bad request');
			break;
		}
	}
	
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
	
	private function explFeaturing($string)
	{
		if(!empty($string)){
			$string = explode(',',trim($string));
			if(!is_array($string)){
				$string = [$string];
			}
			$res = [];
			foreach($string as $str){
				$res[] = [
					'name'=>$str,
					'uri'=>$this->url_safe($str)
				];
			}
			return $res;
		}else{
			return [];
		}
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
	
	//admin funcs
	
	public function countPending($type)
	{
		
		switch($type){
			case 'queue':
				$data = \R::count('mixtape', ' approved="0" AND deleted="0" ') + \R::count('track', ' approved="0" AND is_single="1" AND deleted="0"');
			break;
			case 'artists':
				$data = \R::count('artist', ' pending="1" AND deleted="0" ');
			break;
			case 'comments':
				$data = \R::count('comment', ' seen="0" ');
			break;
			default: $data = [];
		}
		
		return $data;
	}
	
	public function countApproved($type)
	{
		switch($type){
			case 'queue':
				$data = \R::count('mixtape', ' approved="1" AND deleted="0" ') + \R::count('track', ' approved="1" AND is_single="1"  AND deleted="0"');
			break;
			case 'artists':
				$data = \R::count('artist',' pending="0" AND deleted="0" ');
			break;
			case 'comments':
				$data = \R::count('comment',' seen="1" ');
			break;
			default: $data = [];
		}
		return $data;
	}
	
	
	public function getAQueue($k=false,$v=false,$page=1,$c=false,$q=false)
	{
		$per_page = 12;
		if(!$c){
			$per_page /= 2; //divide by 2. the idea is that we always need to provide 12 results. if c is set, were only fetching from that specific table. else we fetch 6 from each.
		}
		$params = [];
		$extra = '';
		if($k != 'deleted'){
			$extra .= ' AND a.deleted="0" ';
		}
		if($q){
			$extra .=' AND (a.title LIKE :fparam OR a.featuring LIKE :sparam OR c.name LIKE :tparam) ';
			$params = [':fparam'=>'%'.$q.'%',':sparam'=>'%'.$q.'%',':tparam'=>'%'.$q.'%'];
		}else{
			$extra .= ' ORDER BY a.added DESC ';
		}
		if($k && is_numeric($v)){
			$tracks = $this->paginatedQuery(
				'SELECT a.*,b.user_name AS user FROM track a INNER JOIN user b ON a.user_id=b.id INNER JOIN artist c ON c.id=a.artist_id WHERE a.'.$k.'="'.$v.'" AND a.is_single="1" '.$extra,
				$params,
				$page,
				$per_page
			);
			$mixtapes = $this->paginatedQuery(
				'SELECT a.*,b.user_name AS user FROM mixtape a INNER JOIN user b ON a.user_id=b.id INNER JOIN artist c ON c.id=a.artist_id WHERE a.'.$k.'="'.$v.'" '.$extra,
				$params,
				$page,
				$per_page
			);
		}else{
			$tracks = $this->paginatedQuery(
				'SELECT a.*,b.user_name AS user FROM track a INNER JOIN user b ON a.user_id=b.id INNER JOIN artist c ON c.id=a.artist_id WHERE a.is_single="1" '.$extra,
				$params,
				$page,
				$per_page
			);
			$mixtapes = $this->paginatedQuery(
				'SELECT a.*,b.user_name AS user FROM mixtape a INNER JOIN user b ON a.user_id=b.id INNER JOIN artist c ON c.id=a.artist_id '.$extra,
				$params,
				$page,
				$per_page
			);
		}
		foreach($tracks as &$track){
			$track['type'] = 0;
			$track['featured'] = (int) $this->exists('featuredsingle','track_id',$track['id']);
		}
		foreach($mixtapes as &$mixtape){
			$mixtape['type'] = 1;
			$mixtape['featured'] = (int) $this->exists('featuredmixtape','mixtape_id',$mixtape['id']);
		}
		if(!$c){
			$array  = array_merge($mixtapes,$tracks);
			$this->array_column_sort($array,'added');
		}else{
			switch($c){
				case 'track':
					$array = $tracks;
				break;
				case 'mixtape':
					$array = $mixtapes;
				break;
				default: throw new \exception('');
				break;
			}
		}
		return $array;
	}
	
	public function adminStatusUpdate($type,$ids,$k,$v)
	{
		\R::exec('UPDATE '.$type.' SET '.$k.'='.$v.' WHERE id IN('.implode(',',$ids).')');
	}
	
	public function toggleFeatured($type,$id,$toggle)
	{
		switch(true){
			case ($type == 'mixtape' && $toggle==1):
				$t = $this->model('featuredmixtape');
				$t->mixtape_id = $id;
				$this->store($t);
			break;
			case ($type == 'mixtape' && $toggle==0):
				$this->delete('featuredmixtape','mixtape_id',$id);
			break;
			case ($type == 'single' && $toggle==1):
				$t = $this->model('featuredsingle');
				$t->track_id = $id;
				$this->store($t);
			break;
			case ($type == 'single' && $toggle==0):
				$this->delete('featuredsingle','track_id',$id);
			break;
		}
		return false;
	}
	
	public function toggleApproved($type,$id,$toggle)
	{
		if($type == 'single') $type = 'track';
		$this->adminStatusUpdate($type,[$id],'approved',$toggle);
		return false;
	}
	
	public function getAArtists($k=false,$v=false,$page=1,$q=false)
	{
		$per_page = 12;
		$extra = '';
		$params = [];
		if($k && is_numeric($v)){
			if($k != 'deleted'){
				$extra .= ' AND deleted="0" ';
			}
			if($q){
				$extra .=' AND name LIKE :fparam ';
				$params = [':fparam'=>'%'.$q.'%'];
			}else{
				$extra .= ' ORDER BY id DESC ';
			}
			$data = $this->paginatedQuery('SELECT * FROM artist WHERE '.$k.'="'.$v.'" '.$extra,$params,$page,$per_page);
		}else{
			if($q){
				$extra .=' WHERE name LIKE :fparam AND deleted="0"';
				$params = [':fparam'=>'%'.$q.'%'];
			}else{
				$extra .= ' WHERE deleted="0" ORDER BY id DESC ';
			}
			$data = $this->paginatedQuery('SELECT * FROM artist '.$extra,$params,$page,$per_page);
		}
		return $data;
	}
	
	public function getAComments($k=false,$v=false,$page=1,$q=false)
	{
		$per_page = 12;
		$extra = '';
		$params = [];
		if($q){
			$extra .=' AND (a.comment LIKE :fparam OR b.user_name LIKE :sparam) ';
			$params = [':fparam'=>'%'.$q.'%',':sparam'=>'%'.$q.'%'];
		}else{
			$extra .= ' ORDER BY a.id DESC ';
		}
		if($k && is_numeric($v)){
			
			$data = $this->paginatedQuery('SELECT a.*,b.user_name FROM comment a JOIN user b ON b.id=a.user_id WHERE a.'.$k.'="'.$v.'" '.$extra,$params,$page,$per_page);
		}else{
			$data = $this->paginatedQuery('SELECT a.*,b.user_name  FROM comment a JOIN user b ON b.id=a.user_id '.$extra,$params,$page,$per_page);
		}
		return $data;
	}
	
	public function getAUsers($k=false,$v=false,$page=1,$q=false)
	{
		$per_page = 12;
		$extra = '';
		$params = [];
		if($k && is_numeric($v)){
			if($k != 'deleted'){
				$extra .= ' AND deleted="0" ';
			}
			if($q){
				$extra .=' AND (user_name LIKE :fparam) ';
				$params = [':fparam'=>'%'.$q.'%'];
			}else{
				$extra .= ' ORDER BY id DESC ';
			}
			$data = $this->paginatedQuery('SELECT * FROM user WHERE id !="0" AND '.$k.'="'.$v.'" '.$extra,$params,$page,$per_page);
		}else{
			if($q){
				$extra .=' AND (user_name LIKE :fparam) ';
				$params = [':fparam'=>'%'.$q.'%'];
			}else{
				$extra .= ' ORDER BY id DESC ';
			}
			$data = $this->paginatedQuery('SELECT * FROM user WHERE id !="0" '.$extra,$params,$page,$per_page);
		}
		return $data;
	}
	
	public function getFilesForDelete($type,$id)
	{
		$files = [
			'image'=>[],
			'audio'=>[],
			'zip'=>[]
		];
		if($type == 'track'){
			$data = \R::getRow('SELECT small,medium,large FROM track WHERE id=:id',[':id'=>$id]);
			foreach($data as $k=>$v){
				array_push($files['image'],array_pop(explode('/',$v)));
			}
			$data = \R::getRow('SELECT audio_file FROM track WHERE id=:id',[':id'=>$id]);
			foreach($data as $k=>$v){
				array_push($files['audio'],array_pop(explode('/',$v)));
			}
		}else{
			$data = \R::getAll('SELECT audio_file FROM track WHERE mixtape_id=:id',[':id'=>$id]);
			foreach($data as $d){
				foreach($d as $k=>$v){
					array_push($files['audio'],array_pop(explode('/',$v)));
				}
			}
			$data = \R::getRow('SELECT small,medium,large FROM mixtape WHERE id=:id',[':id'=>$id]);
			foreach($data as $k=>$v){
				array_push($files['image'],array_pop(explode('/',$v)));
			}
			$data = \R::getRow('SELECT zip FROM mixtape WHERE id=:id',[':id'=>$id]);
			foreach($data as $k=>$v){
				if($v!=''){
					array_push($files['zip'],$v);
				}
			}
		}
		return $files;
	}
	
	public function softDelete($type,$id)
	{
		\R::exec('UPDATE '.$type.' SET deleted="1" WHERE id=:id',[':id'=>$id]);
	}
	
	public function banUser($user_id)
	{
		\R::exec('UPDATE user SET banned="1" WHERE id=:id',[':id'=>$user_id]);
		$email = \R::getCell('SELECT email FROM user WHERE id=:id',[':id'=>$user_id]);
		$this->cachedCall('fetchUser',[$email],0,true); //cachedCall caches db queries. the optional 4th parameter instructs the script to ignore cache record and force update.
		return true;
	}
	
	public function unbanUser($user_id)
	{
		\R::exec('UPDATE user SET banned="0" WHERE id=:id',[':id'=>$user_id]);
		$email = \R::getCell('SELECT email FROM user WHERE id=:id',[':id'=>$user_id]);
		$this->cachedCall('fetchUser',[$email],0,true); //cachedCall caches db queries. the optional 4th parameter instructs the script to ignore cache record and force update.
		return true;
	}
	
	public function getArtistById($artist_id)
	{
		return \R::getRow('SELECT * FROM artist WHERE id=:id',[':id'=>$artist_id]);
	}
	
	public function getTrackById($id)
	{
		return \R::getRow('SELECT a.*,b.name AS artist FROM track a JOIN artist b on b.id=a.artist_id WHERE a.id=:id',[':id'=>$id]);
	}
	
	public function getMixtapeById($id)
	{
		return \R::getRow('SELECT a.*,b.name AS artist FROM mixtape a JOIN artist b on b.id=a.artist_id WHERE a.id=:id',[':id'=>$id]);
	}
	
	public function getBanners()
	{
		return \R::getRow('SELECT * FROM banner WHERE id="1"');
	}
}