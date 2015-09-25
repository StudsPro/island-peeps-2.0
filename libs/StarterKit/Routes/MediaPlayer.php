<?php

namespace StarterKit\Routes;

class MediaPlayer extends ViewController
{
	use \StarterKit\Traits\SignedPolicy;
	public $app;
	function __construct()
	{
		$this->app = \StarterKit\App::getInstance();
		$this->app->args['scripts'] = [
			'player/build/mediaelement-and-player.min.js',
			'player/build/mep-feature-playlist.js',
			'player.js',
			'paginater.js'
		];
		
		$this->app->args['scripts_external'] = [
			'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js',
			'https://cdnjs.cloudflare.com/ajax/libs/livestamp/1.1.2/livestamp.min.js'
		];
		
		if($this->app->is_mobile() || isset($this->app->get['m'])){
			$this->app->args['mobile'] = true;
		}else{
			$this->app->args['mobile'] = false;
		}
			
		
		$this->app->args['styles'] = 'player/css/PlayerStyle.css';
		
		$this->app->args['styles_external'] = '//maxcdn.bootstrapcdn.com/font-awesome/4.4.0/css/font-awesome.min.css';
		
		parent::__construct();
	}
	
	public function single($id,$uri)
	{
		$app = $this->app;
		$db = $app->db;
		$args = $app->args;
		$args['single'] = $db->cachedCall('getTrack',[$id],30);
		if(empty($args['single'])){
			$app->pass();
		}
		if($uri !== $args['single']['uri']){
			$app->redirect('/s'.$id.'/'.$args['single']['uri'],301);
		}
		
		
		$args['title'] = $args['single']['artist_name'] .' - '.$args['single']['title'];
		if(!empty($args['single']['featuring'])){
			$args['title'] .= ' ft. '. implode(', ',array_column($args['single']['featuring'],'name'));
		}
		if($app->is_user()){
			$args['user_fave'] = $app->session['user']->hasFave('single',$id) ? 'active' : '';
			$args['user_like'] = $app->session['user']->hasLike('single',$id) ? 'active' : '';
		}
		
		$args['related'] = $db->getRelated('single',$args['single']['id'],$args['mobile']);

		$args['meta_description'] = !empty($args['single']['description']) ? $args['single']['description'] : $args['title'];
		$args['meta_img'] = $args['single']['large'];
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/media_single.twig',$args);
		}else{
			parent::render('media_single.twig',$args);
		}
	}
	
	public function mixtape($id,$uri)
	{
		$app = $this->app;
		$db = $app->db;
		$args = $app->args;
		$args['mixtape'] = $db->cachedCall('getMixtape',[$id],30);
		if(empty($args['mixtape'])){
			$app->pass();
		}
		if($uri !== $args['mixtape']['uri']){
			$app->redirect('/m'.$id.'/'.$args['mixtape']['uri'],301);
		}
		
		$args['title'] = $args['mixtape']['artist_name'] .' - '.$args['mixtape']['title'];
		if(!empty($args['mixtape']['featuring'])){
			$args['title'] .= ' ft. '. implode(', ',array_column($args['mixtape']['featuring'],'name'));
		}
		if($app->is_user()){
			$args['user_fave'] = $app->session['user']->hasFave('mixtape',$id) ? 'active' : '';
			$args['user_like'] = $app->session['user']->hasLike('mixtape',$id) ? 'active' : '';
		}
		
		$args['meta_description'] = !empty($args['mixtape']['description']) ? $args['mixtape']['description'] : $args['title'];
		$args['meta_img'] = $args['mixtape']['large'];
		
		$args['related'] = $db->getRelated('mixtape',$args['mixtape']['id'],$args['mobile']);

		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/media_mixtape.twig',$args);
		}else{
			parent::render('media_mixtape.twig',$args);
		}
	}
}
