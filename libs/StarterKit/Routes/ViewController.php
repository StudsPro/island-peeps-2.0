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
		$this->twig->addExtension(new \Twig_Extension_StringLoader());
	}
	
	public function __call($method,$args = null)
	{
		$this->app->pass();
	}
	
	public function render($template,$args)
	{
		$args['notifications'] = $this->app->session['notifications'];
		echo $this->twig->loadTemplate($template)->render($args);
		$this->app->session['notifications'] = [];
	}
	
	public function cachedRender($template,$args,$expiry=0)
	{
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
}