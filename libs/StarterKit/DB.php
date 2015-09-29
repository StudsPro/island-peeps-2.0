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
	public function fetchAdmin($email)
	{
		return \R::getRow('SELECT * FROM admin WHERE email=:email LIMIT 1',[':email'=>$email]);
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
	
	public function getMasterList()
	{
		$data = \R::getAll('SELECT a.*,b.name AS affiliate,c.name AS type FROM masterlist a INNER JOIN admin b ON a.admin_id=b.id JOIN type c ON a.type_id=c.id');
		
		return $data;
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