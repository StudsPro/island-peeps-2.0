<?php

namespace StarterKit;

class Cache
{
	private static $instance = null;
	
	private $driver;
	
	protected function __construct($config)
	{
		if(empty($config)){ throw new \exception('invalid!'); }
		$c = new \Memcached('sk_cache');
		//$c->resetserverlist();
		$config_servers = $config['servers']; //easier to keep the code straight this way.
		$used_servers   = $c->getServerList();
		$count_servers_used = count($used_servers);
		$count_servers_config = (count($config_servers));
		if(!$count_servers_used){
			//we have no servers stored. store the entire array.
			
			$c->addServers($config_servers);
		}else{
			//we need to check to see if a new server was added to the config.
			if($count_servers_used < $count_servers_config){
				$used = array_column($used_servers,0); //0 index is ip, 1 index is port...
				$config = array_column($config_servers,0);
				usort($used,['self','sortServers']);
				usort($config,['self','sortServers']);
				$diff = $count_servers_config - $count_servers_used;
				$new = array_slice($config,$count_servers_used -1);
				foreach($new as $server){
					$c->addServer($server,11211); //to do: find a way to retrieve the port of the server when adding it, in case of people with custom ports for memcache.
				}
			}
		}
		$this->driver = $c;
		self::$instance = &$this;	
	}
	
	public static function getInstance($config = false)
	{
		return (is_null(self::$instance) ? self::$instance = new self($config) : self::$instance);
	}
	
	private static function sortServers($a,$b)
	{
		$a_arr = explode('.', $a);
		$b_arr = explode('.', $b);
		foreach (range(0,3) as $i) {
			if ( $a_arr[$i] < $b_arr[$i] ) {
				return -1;
			}
			elseif ( $a_arr[$i] > $b_arr[$i] ) {
				return 1;
			}
		}
		return -1;
	}
	
	public function get($k)
	{
		$v = $this->driver->get($k);
		return (($this->driver->getResultCode() == \Memcached::RES_NOTFOUND) ? -1 : $v); //somethings could return false, so -1 is used instead
	}
	
	public function getAndTouch($k,$expiry=60)
	{
		$v = $this->driver->get($k);
		if( $this->driver->getResultCode() !== \Memcached::RES_NOTFOUND ){
			$this->driver->set($k,$v,$expiry);
			return $v;
		}
		return -1;
	}
	
	/*
	public function touch($k,$expiry=60)
	{
		//unfortunely hhvm does not implement Mecached::touch()
		return $this->driver->touch($k,$expiry);
	}
	*/
	
	public function set($k,$v,$e = 0)
	{
		// key + val + expire time (default: never expires)
		$this->driver->set($k,$v,$e);
		return (($this->driver->getResultCode() == \Memcached::RES_SUCCESS) ? true : false);
	}
	
	public function rm($k)
	{
		$this->driver->delete($k);
	}
	
	public function rmBulk($keys)
	{
		foreach($keys as $k){
			$this->rmCache($k); //because HHVM doesnt implement Memcached :: deleteMulti() :(
		}	
	}
	
	public function mustSolveCaptcha($ip,$failed = false) //if failed = true, we increment the key and increase expiry 1 hour
	{
		$num = 0;
		$expiry = time() + (60 * 60 * 1 ); // expiry always adds 1 hour
		$key = 'ReCaptcha:'.$ip;
		$val = $this->get($key);
		if($val === -1){
			$this->set($key,$num, $expiry );
		}else{
			$num = $val;
		}
		if($failed){
			$num = $this->driver->increment($key,1,$num,$expiry);
		}
		return ($num > 3 ? true : false);
	}
	
	public function shutdown()
	{
		$this->driver->quit();
	}
}