<?php

$path = '/usr/share/nginx/html/islandpeeps.com/dev/html/uploads/';

$fs = glob($path.'*.mp4');

foreach($fs as $f)
{
	if(is_file($f) && !is_dir($f)){
		$input = $f;
		$output = rtrim($f,'.mp4') . '.png';
		shell_exec('avconv -ss 5 -i '.escapeshellarg($input).' -r 0.0033 -vf scale=-1:120 -vcodec png '.escapeshellarg($output));
	}
}