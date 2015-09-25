<?php

namespace StarterKit\Routes;

class EmbedPlayer extends ViewController
{
	use \StarterKit\Traits\SignedPolicy;
	public $app;
	function __construct()
	{
		$this->app = \StarterKit\App::getInstance();
		parent::__construct();
	}
	
	public function single($id)
	{
		$app = $this->app;
		$db = $app->db;
		$args = $app->args;
		$args['single'] = $db->cachedCall('getTrack',[$id],60);
		if(empty($args['single'])){
			die('Invalid Embed ID');
		}
		$args['title'] = $args['single']['artist_name'] .' - '.$args['single']['title'];
		if(!empty($args['mixtape']['featuring'])){
			$args['title'] .= ' ft. '. implode(', ',array_column($args['single']['featuring'],'name'));
		}
		parent::render('embed_single.twig',$args);
	}
	
	public function mixtape($id)
	{
		$app = $this->app;
		$db = $app->db;
		$args = $app->args;
		$args['mixtape'] = $db->cachedCall('getMixtape',[$id],60);
		if(empty($args['mixtape'])){
			die('Invalid Embed ID');
		}
		$args['title'] = $args['mixtape']['artist_name'] .' - '.$args['mixtape']['title'];
		if(!empty($args['mixtape']['featuring'])){
			$args['title'] .= ' ft. '. implode(', ',array_column($args['mixtape']['featuring'],'name'));
		}
		parent::render('embed_mixtape.twig',$args);
	}
	
	
}