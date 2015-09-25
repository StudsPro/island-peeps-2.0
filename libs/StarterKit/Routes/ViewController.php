<?php

namespace StarterKit\Routes;

class ViewController
{
	public $app;
	public $twig;
	function __construct($template_path = false)
	{
		$this->app = \StarterKit\App::getInstance();
		$template_path = ($template_path) ? $template_path : $this->app->twig_config['template_path'];
		$this->twig = new \Twig_Environment( new \Twig_Loader_Filesystem( $template_path ) );
	}
	
	public function __call($method,$args = null)
	{
		$this->app->pass();
	}
	
	public function render($template,$args)
	{
		$args = $this->precheck($args);
		echo $this->twig->loadTemplate($template)->render($args);
	}
	
	public function cachedRender($template,$args,$expiry=0)
	{
		$args = $this->precheck($args);
		$cache = $this->app->cache;
		$key = 'twig_'.md5($template);
		$html = $cache->get($key);
		if($html == -1){
			$args['cached'] = true;
			$html = $this->twig->loadTemplate($template)->render($args);
			$cache->set($key,$html,$expiry);
		}
		echo $html;
	}
	public function cachedTemplateExists($template)
	{
		$cache = $this->app->cache;
		$html = $cache->get('twig_'.md5($template));
		if($html == -1){
			return false;
		}
		return $html;
	}
	
	private function precheck($args)
	{
		$app = $this->app;
		if($app->minify){
			if(!empty($args['scripts'])){
				if(is_array($args['scripts'])){
					foreach($args['scripts'] as &$script)
					{
						if(strpos($script,'/') !== true){
							$x = explode('.',$script);
							if(count($x) === 2){
								$script = $x[0].'.min.'.$x[1];
							}
						}
					}
				}else{
					if(strpos($args['scripts'],'/') !== true){
						$x = explode('.',$args['scripts']);
						if(count($x) === 2){
							$args['scripts'] = $x[0].'.min.'.$x[1];	
						}	
					}
				}
			}
			if(!empty($args['styles'])){
				if(is_array($args['styles'])){
					foreach($args['styles'] as &$script)
					{
						if(strpos($script,'/') !== true){
							$x = explode('.',$script);
							if(count($x) === 2){
								$script = $x[0].'.min.'.$x[1];
							}
						}
					}
				}else{
					if(strpos($args['styles'],'/') !== true){
						$x = explode('.',$args['styles']);
						if(count($x) === 2){
							$args['styles'] = $x[0].'.min.'.$x[1];	
						}	
					}
				}
			}
		}
		return $args;
	}
}