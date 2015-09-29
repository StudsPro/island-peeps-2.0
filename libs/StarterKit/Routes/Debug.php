<?php

namespace StarterKit\Routes;

class Debug extends ViewController
{
	public $app;
	function __construct()
	{
		$this->app = (\StarterKit\App::getInstance());
		parent::__construct();
	}
	
	public function extensions()
	{
		$this->pretty(get_loaded_extensions());
	}
	
	private function pretty($what)
	{
		echo '<pre>'.print_r($what,true).'</pre>';
	}
	
	private function dump($what)
	{
		var_dump($what);
	}
	
	public function admin()
	{
		if(isset($this->app->session['admin'])){
			$this->pretty($this->app->session['admin']);
		}else{
			echo 'no session';
		}
	}
	
	public function user()
	{
		if(isset($this->app->session['user'])){
			$this->pretty($this->app->session['user']);
		}else{
			echo 'no session';
		}
	}
	
	public function tables()
	{
		$this->pretty(
			$this->app->db->getTables()
		);
	}
	
	public function table_exists()
	{
		$get = $this->app->get;
		$table = (isset($get['table'])) ? $get['table'] : 'video';
		$this->dump($this->app->db->tableExists($table));
	}
	
	public function session_metrics()
	{
		$sess = serialize($this->app->session['user']);
		$size = mb_strlen($sess,'8bit');
		
		$gb = 1000000000;
		
		$total = $gb / $size;
		
		echo 'the size of a session is '.$size.' bytes . we can store '.$total.' base user sessions per 1gb of ram';
	}
	
	public function post_test()
	{
		$app = $this->app;
		if($app->slim->request->isPost()){
			$this->pretty($app->post);
			$this->pretty($_POST);
		}else{
			$html =
			'
			<!DOCTYPE html>
			<html>
			<head>
				<meta charset="utf-8"/>
				<title>test</title>
			</head>
			<body>
			<form method="post" style="width:150px;">
				<select multiple name="regions[]" style="width:100%;display:block">
			';
			for($i=0;$i<20;$i++){
				$html .='<option value="'.$i.'">'.$i.'</option>';
			}
			$html .= '</select></br><input type="submit" value="submit" style="width:100%; display: block"></form></body></html>';
			echo $html;
		}
	}
}