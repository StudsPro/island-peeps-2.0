<?php

namespace StarterKit\Routes;

class Artists extends ViewController
{
	public $app;
	function __construct()
	{
		$this->app = \StarterKit\App::getInstance();
		parent::__construct();
	}
	
	public function artists()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Hip Hop Artists';
		$args['description'] = 'Browse Hip-Hop Artists at WixTape.com';
		$args['keywords'] = 'Hip Hop, Hip Hop Artists,Hip Hop Mixtapes, Wixtape, Hip Hop Singles';
		$args['artists'] = $app->db->cachedCall('getArtists',[],60,false,false);
		$args['scripts'] = 'paginater.js';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/artists.twig',$args);
		}else{
			parent::render('artists.twig',$args);
		}
	}
	
	public function artist($artist)
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['artist'] = $app->db->cachedCall('artistProfile',[$artist]);
		if(empty($args['artist'])){
			$app->pass();
		}
		$args['title'] = $args['artist']['name'];
		$args['meta_description'] = $args['title'].' \'s Artist Profile at WixTape.com';
		if($args['artist']['pending'] == 1)
		{
			$args['no_follow'] = true;	
		}
		
		$args['scripts'] = 'paginater.js';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/artist_profile.twig',$args);
		}else{
			parent::render('artist_profile.twig',$args);
		}
	}
}