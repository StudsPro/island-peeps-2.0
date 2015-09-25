<?php

namespace StarterKit;

class Recover
{
	public $success;
	public $details;
	private $db;
	function __construct($token)
	{
		$db = $this->db = \StarterKit\DB::getInstance();
		$this->details = $db->fetchRecoverDetails($token);
		if(empty($this->details)){
			throw new \exception('Invalid Recovery Token');
		}
	}

	public function approve()
	{
		$details = $this->details;
		if((\StarterKit\App::getInstance())->remote_addr == $details['remote_addr']){
			$this->success = true;
		}
		return $this;
	}
	
	public function cancel()
	{
		$db = $this->db;
		$details = $this->details;
		if((\StarterKit\App::getInstance())->remote_addr == $details['remote_addr']){
			$db->trash('recover',$details['id']);
			$this->success = true;
		}
		return $this;
	}
}