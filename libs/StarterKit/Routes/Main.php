<?php

namespace StarterKit\Routes;

class Main extends ViewController
{
	use \StarterKit\Traits\Upload;
	public $app;
	function __construct()
	{
		$this->app = (\StarterKit\App::getInstance());
		parent::__construct();
	}
	
	public function index()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['featured'] = [
			'singles' => $app->db->getFeatured('singles'),
			'mixtapes' => $app->db->getFeatured('mixtapes')
		];
		$args['banners'] = $app->db->cachedCall('getBanners',[]);
		$args['scripts'] = 'paginater.js';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/index.twig',$args);
		}else{
			parent::render('index.twig',$args);
		}
	}
	
	
	public function contact()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Contact Us';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/contact.twig',$args);
		}else{
			parent::render('contact.twig',$args);
		}
	}
	
	public function terms_service()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Terms of Service';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/terms_service.twig',$args);
		}else{
			parent::render('terms_service.twig',$args);
		}
	}
	
	public function dmca_complaints()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'DMCA Complaints';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/dmca_complaints.twig',$args);
		}else{
			parent::render('dmca_complaints.twig',$args);
		}
	}
	
	public function staff()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Staff';
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/staff.twig',$args);
		}else{
			parent::render('staff.twig',$args);
		}
	}
	
	public function create_account()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		if($app->is_user()){
			$app->redirect('/user');
		}
		$args['no_follow'] = true;
		if($app->is_mobile() || isset($app->get['m'])){
			parent::cachedRender('mobile/create_account.twig',$args);
		}else{
			parent::cachedRender('create_account.twig',$args);
		}
	}
	
	public function login()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		if($app->is_user()){
			$app->redirect('/user');
		}
		$args['no_follow'] = true;
		if($app->is_mobile() || isset($app->get['m'])){
			parent::cachedRender('mobile/login.twig',$args);
		}else{
			$app->redirect('/?etab=login');
		}
	}
	
	public function singles()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		if(isset($get['timespan'])){
			$args['timespan'] = $get['timespan'];
		}else{
			$args['timespan'] = 'this_week';
		}
		if(isset($get['sort'])){
			$args['sort'] = $get['sort'];
		}else{
			$args['sort'] = 'popular';
		}
		$args['singles'] = $app->db->getSingles(1,$args['sort'],$args['timespan']);
		$args['scripts_external'] = [
			'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js',
			'https://cdnjs.cloudflare.com/ajax/libs/livestamp/1.1.2/livestamp.min.js'
		];
		$args['title'] = 'Hip Hop Singles';
		$args['description'] = 'Browse Hip Hop Singles at WixTape.com';
		$args['keywords'] = 'Hip Hop, Wixtape, Hip Hop Singles, New Hip Hop';
		$args['scripts'] = ['tbl.js','paginater.js'];
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/singles.twig',$args);
		}else{
			parent::render('singles.twig',$args);
		}
	}
	
	public function mixtapes()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		
		$page = 1;
		if(isset($get['timespan'])){
			$args['timespan'] = $get['timespan'];
		}else{
			$args['timespan'] = 'this_week';
		}
		if(isset($get['sort'])){
			$args['sort'] = $get['sort'];
		}else{
			$args['sort'] = 'popular';
		}
		$args['mixtapes'] = $app->db->getMixtapes(1,$args['sort'],$args['timespan']);
		$args['scripts_external'] = [
			'https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.10.6/moment.min.js',
			'https://cdnjs.cloudflare.com/ajax/libs/livestamp/1.1.2/livestamp.min.js'
		];
		$args['title'] = 'Hip Hop Mixtapes';
		$args['description'] = 'Browse Hip Hop Mixtapes at WixTape.com';
		$args['keywords'] = 'Hip Hop, Wixtape, Hip Hop Mixtapes, New Hip Hop';
		$args['scripts'] = ['tbl.js','paginater.js'];
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/mixtapes.twig',$args);
		}else{
			parent::render('mixtapes.twig',$args);
		}
	}
	
	public function unsupported_browser()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['title'] = 'Browser Not Supported!';
		$args['no_follow'] = true;
		parent::render('unsupported_browser.twig',$args);
	}
	
	public function search()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$filter = $app->filter;
		if(isset($get['q'])){
			$args['query'] = $filter->chain(['min','rmnl'],$get['q']);
		}else{
			$app->pass();
		}
		$args['no_follow'] = true;
		$args['results'] = $app->db->search($args['query']);
		//echo '<pre>'.print_r($args['results'],true).'</pre>'; die;
		$args['scripts'] = ['tbl.js','paginater.js'];
		if($app->is_mobile() || isset($app->get['m'])){
			parent::render('mobile/search.twig',$args);
		}else{
			parent::render('search.twig',$args);
		}
	}
	
	public function forgot_password()
	{
		$app = $this->app;
		$get = $app->get;
		$args = $app->args;
		$args['no_follow'] = true;
		parent::cachedRender('forgot_password.twig',$args);
	}
	
	public function reset_password()
	{
		try{
			if(isset($this->app->get['token']) && isset($this->app->get['action'])){
				switch($this->app->get['action']){
					case 'CONFIRM':
						if( ( new \StarterKit\Recover($this->app->get['token']) )->approve()->success ){
							$this->app->args['title'] .= 'Reset Password';
							$this->app->args['reset_token']  = $this->app->get['token'];
							if($this->app->is_mobile() || isset($this->app->get['m'])){
								parent::render('mobile/reset_password.twig',$this->app->args);
							}else{
								parent::render('reset_password.twig',$this->app->args);
							}
						}
					break;
					case 'CANCEL':
						if((new \StarterKit\Recover($this->app->get['token']))->cancel()->success){
							echo 'The Recovery request was cancelled.';
						}
					break;
					default:
						$this->app->pass();
					break;
				}
			}else{
				$this->app->pass();
			}
		}
		catch(\exception $e){
			$this->app->pass();
		}
	}
	
	public function download()
	{
		$app = $this->app;
		$get = $app->get;
		$db = $app->db;
		$response = $app->slim->response;
		if(!isset($get['type'])){
			$app->pass();
		}
		if(!isset($get['id'])){
			$app->pass();
		}
		
		switch($get['type']){
			case 'mixtape':
				$data = $db->getDownloadableMixtape($get['id']);
				if(empty($data)){
					die('The file doesnt exist or does not allow downloading');
				}
			break;
			case 'single':
				$data = $db->getDownloadableSingle($get['id']);
				if(empty($data)){
					die('The file doesnt exist or does not allow downloading');
				}
			break;
			default:
				die('The type of download specified was invalid.');
		}
		
		$config = (\StarterKit\App::getInstance())->aws_config;
		$s3 = new \Aws\S3\S3Client($config['config']);
		$bucket = $config['mp3bucket'];
		if(isset($data['tracks'])){
			if($data['zip'] == ''){
				$mixtape = $db->model('mixtape',$data['id']);
				//its a mixtape
				$file_type = 'application/zip';
				$file_name = $this->safe_name($data['title']) . '.zip';
				$zip = new \ZipArchive;
				$zip->open('/tmp/'.$file_name, \ZipArchive::CREATE);
				foreach($data['tracks'] as $track){
					$result = $s3->getObject([
						'Bucket' => $bucket,
						'Key'    => $track['audio_file']
					]);
					if(!empty($result['Body'])){
						$zip->addFromString($this->safe_name($track['title']).'.mp3',$result['Body']);
					}
				}
				$zip->close();
				$file = file_get_contents('/tmp/'.$file_name);
				$file_size = strlen($file);
				$s3->putObject([
					'Bucket'=>$config['zipbucket'],
					'Key'=>$file_name,
					'Body'=>$file,
					'CacheControl'=>'max-age=172800',
					'Content-Type'=>$file_type
				]);
				$mixtape->zip = $file_name;
				$db->store($mixtape);
			}else{
				//stream from bucket
				$result = $s3->getObject([
					'Bucket' => $config['zipbucket'],
					'Key'    => $data['zip']
				]);
				$file_name = $data['zip'];
				$file = $result['Body'];
				$file_size = strlen($file);
				$file_type = 'application/zip';
			}
			
		}else{
			//its a single
			$file_type = 'audio/mpeg';
			$file_name = $this->safe_name($data['title']).'.mp3';
			
			$result = $s3->getObject([
				'Bucket' => $bucket,
				'Key'    => $data['audio_file']
			]);
			$file = $result['Body'];
			$file_size = strlen($file);
		}
		
		$response->headers->set('Content-Type', $file_type);
		$response->headers->set('Pragma', "public");
		$response->headers->set('Content-disposition:', 'attachment; filename=' . $file_name);
		$response->headers->set('Content-Transfer-Encoding', 'binary');
		$response->headers->set('Content-Length', $file_size);
		$response->setBody($file);
	}
	
}