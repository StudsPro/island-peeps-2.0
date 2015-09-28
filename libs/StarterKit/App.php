<?php

namespace StarterKit;

class App
{
	
	private static $instance = null;
	
	public $session;
	public $post;
	public $files;
	public $get;
	public $args;
	public $public_html;
	
	public $twig_config;
	public $smtp_config;

	
	public $debug;
	public $remote_addr;
	
	public $cache  = null;
	public $slim   = null;
	public $db     = null;
	public $filter = null;

	protected function __construct($config)
	{
		if(!$config){
			throw new \exception('`$config` cant be null when constructing StarterKit');
		}
		if($config['strict']){
			error_reporting( E_ALL | E_NOTICE | E_STRICT );
		}
		
		$this->public_html = $config['public'];

		$this->debug = $config['debug'];
		date_default_timezone_set($config['timezone']);
		
		$this->slim  = new \Slim\Slim( $config['slim_args'] );
		$this->twig_config = $config['twig_args'];
		$this->aws_config  = $config['aws_args'];
		$this->smtp_config      = $config['smtp_args'];
		$this->files   = &$_FILES;
		$this->get     = $this->slim->request()->get();
		$this->post    = $this->slim->request()->post();
		
		if($config['session_args']['type'] === 'redis'){
			ini_set('session.save_handler', 'redis');
			ini_set('session.timeout',18000);
			// each client should remember their session id for EXACTLY 1 hour
			session_set_cookie_params(18000);
			ini_set('session.save_path', 'tcp://'. $config['session_args']['host'] .':'. $config['session_args']['port']);    
		}
		session_name($config['session_args']['name']);
		session_start();
		$this->session = &$_SESSION;
		$this->remote_addr = $this->slim->request->getIp();
		
		//sometimes you might need to set additional args. you could allow middleware to add args here, or virtually anything else you want
		$base_url = $config['scheme'].$_SERVER['SERVER_NAME'];

		$extra_template_args = [
			'csrf'=>$this->csrf(),
			'base_url'=>$base_url.'/',//root url
			'year'=>date('Y'), //its here because date timezone must be set first
			'styles'=>'', //allow subtemplate to pass extra styles to header.twig
			'scripts'=>'', //allow subtemplate to pass extra script to footer.twig
			'styles_external'=>'',//remote stylesheets. must have fully qualified url
			'scripts_external'=>'',//remote scripts. same as above
			'user'=>$this->is_user() ? $this->session['user'] : false,
			'admin'=>$this->is_admin() ? $this->session['admin'] : false,
			'url'=>$this->getUrl($base_url),
			'canonical'=>$base_url.$this->slim->request()->getResourceUri(),
		]; 
		
		$this->args = array_merge($config['template_args'],$extra_template_args);
		
		//todo make these all lazy load for performance.
		$this->cache  = \StarterKit\Cache::getInstance($config['cache_args']);
		$this->db = \StarterKit\DB::getInstance($config['db_args']);
		$this->filter = \RedBeanFVM\RedBeanFVM::getInstance();
		$this->smtp  = \StarterKit\Email::getInstance($config['smtp_args']);
		
		self::$instance = &$this;
	}
	
	private function getUrl($base_url)
	{
		$url = $base_url.$this->slim->request()->getResourceUri();
		if(!empty($this->get)){
			$url.= '?'.http_build_query($this->get);
		}
		return $url;
	}
	
	private function csrf()
	{
		if(!isset($this->session['csrf'])){
			$this->session['csrf'] = str_replace('+','',base64_encode(openssl_random_pseudo_bytes(16)));
		}
		return $this->session['csrf'];
	}
	
	public function __before()
	{		
		if(isset($_COOKIE['sk_restore'])){
			if(!$this->is_user()){
				if(\StarterKit\User::restore($_COOKIE['sk_restore']) === true){
					if(!$this->is_user()){
						$this->session['user'] = $this->args['user'] = $_SESSION['user'];
					}
				}
			}
		}
		
		if($this->is_user()){
			$this->session['user']->refresh();
		}
	}
	
	
	public function __beforeRouter()
	{
		
	}
	
	public function __beforeDispatch()
	{
		
	}
	
	public function __after()
	{
		$this->cache->shutdown();
	}
	
	public static function getInstance($config = false)
	{
		return (is_null(self::$instance) ? self::$instance = new self($config) : self::$instance);
	}
	
	public static function autoload($class)
	{
		$file =  __DIR__ . str_replace('\\','/', preg_replace('/'. __NAMESPACE__ .'/','',$class,1)) . '.php';
		if(file_exists($file)){
			include $file;
		}
	}
	
	public static function registerAutoloader()
	{
		spl_autoload_register('\\StarterKit\\App::autoload');
	}
	
	public function __call($name, $args = false)
	{
		$callable = $this->slim;
		if(method_exists($callable,$name)){
			if($args)
				return call_user_func_array( [$callable,$name] , $args);
			else
				return call_user_func( [$callable,$name] );
		}
		throw new \exception('method doesnt exist::'.$name);
	}
	
	public function is_admin()
	{
		if( isset( $this->session['admin'] ) ) {
			return (bool) ($this->session['admin'] instanceof \StarterKit\Admin);
		}else{
			return false;
		}
	}
	
	public function is_user()
	{
		if( isset( $this->session['user'] ) ) {
			return (bool) ($this->session['user'] instanceof \StarterKit\User);
		}else{
			return false;
		}
	}
	
	public function get_request($url)
	{
		$curl = curl_init();
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL => $url,
			CURLOPT_USERAGENT => 'Spider'
		));
		$resp = curl_exec($curl);
		curl_close($curl);
		unset($curl);
		return $resp;
	}
}