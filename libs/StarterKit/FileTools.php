<?php

namespace StarterKit;

class FileTools
{
	use \StarterKit\Traits\Upload;
	
	private $fails = 0;
	private $last = null;
	
	function __construct(){}
	
	function __call($fn,$args=[])
	{
		if($this->last == $fn){
			$this->fails +=1;
		}
		$this->last = $fn;
		if($this->fails > 5){
			throw new \exception('Unable to execute private function from public scope.');
		}else{
			call_user_func_array([$this,$fn],$args);
		}
	}
}