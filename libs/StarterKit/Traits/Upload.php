<?php

namespace StarterKit\Traits;

trait Upload
{
	//private methods
	private function zimg_upload($img_name,$files,$min_width = 120, $min_height = 120)
	{
		//leaving this method here in case we decide we need to do image processing in php, since zimg provides high performance image manipulation.
		if(empty($files) || !isset($files[$img_name])){
			throw new \exception('You must select an image!');
		}
		if($files[$img_name]['error'])
		{
			throw new \exception($this->upload_errors($files[$img_name]['error']));
		}
		$image_temp = $files[$img_name]['tmp_name'];
		@$image_info = getimagesize($image_temp);
		$exif = [IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG];
		$finfo  = ['image/gif','image/png','image/jpeg','image/pjpeg'];
		$etype = exif_imagetype($image_temp);
		if(!function_exists('finfo_file')){
			throw new \exception('Unable to determine image mime type.');
		}
		$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE),$image_temp);
		if( !is_uploaded_file($files[$img_name]['tmp_name']) ){
			throw new \exception('bad file input');
		}
		if( !in_array($etype,$exif) || !in_array($type,$finfo)){
			throw new \exception('file type not allowed');
		}
		switch($type){
			case 'image/gif':
				$type2 = 'gif';
			break;
			case 'image/png':
				$type2 = 'png';
			break;
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$type2 = 'jpeg';
			break;
		}
		if( !$image_info ){
			throw new \exception('invalid image size.');
		}
		$width  = $image_info[0];
		$height = $image_info[1];
		if($width < $min_width || $height < $min_height){
			throw new \exception('Images must be atleast 120 x 120 in size.');
		}
		$ch = curl_init('http://127.0.0.1:4869/upload');
		curl_setopt($ch,CURLOPT_HTTPHEADER, ['Content-Type:'.$type2]);
		curl_setopt($ch, CURLOPT_BINARYTRANSFER, true);
		curl_setopt($ch,CURLOPT_POST,1);
		curl_setopt($ch,CURLOPT_POSTFIELDS, file_get_contents($image_temp) );
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		$result = json_decode(curl_exec($ch),false);
		curl_close($ch);
		if($result->ret == false){
			throw new \exception($result->error->message);
		}
		return $result->info->md5;
	}
	
	private function square_thumbs($img_name,$files,$sizes = [60,220,800],$min_width=120,$min_height=120)
	{
		if(empty($files) || !isset($files[$img_name])){
			throw new \exception('You must select an image!');
		}
		if($files[$img_name]['error'])
		{
			throw new \exception($this->upload_errors($files[$img_name]['error']));
		}
		$image_temp = $files[$img_name]['tmp_name'];
		@$image_info = getimagesize($image_temp);
		$exif = [IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG];
		$finfo  = ['image/gif','image/png','image/jpeg','image/pjpeg'];
		$etype = exif_imagetype($image_temp);
		if(!function_exists('finfo_file')){
			throw new \exception('Unable to determine image mime type.');
		}
		$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE),$image_temp);
		if( !is_uploaded_file($files[$img_name]['tmp_name']) ){
			throw new \exception('bad file input');
		}
		if( !in_array($etype,$exif) || !in_array($type,$finfo)){
			throw new \exception('file type not allowed');
		}
		switch($type){
			case 'image/gif':
				$type2 = 'gif';
			break;
			case 'image/png':
				$type2 = 'png';
			break;
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$type2 = 'jpg';
			break;
		}
		if( !$image_info ){
			throw new \exception('invalid image size.');
		}
		
		//get the image resource
		$image = imagecreatefromstring(file_get_contents($image_temp));
		$width  = $image_info[0];
		$height = $image_info[1];
		if($width < $min_width || $height < $min_height){
			throw new \exception('Images must be atleast 120 x 120 in size.');
		}
		if($width != $height){
			list($size,$image) = $this->square_img($image,$width,$height);
			$width = $height = $size;
		}
		$size = $width;
		//now we have a square image and our image size is updated to reflect this change.
		$res = [];
		foreach($sizes as $z)
		{
			$t = imagecreatetruecolor($z,$z);
			ob_start();
			switch($type){
				case 'image/gif':
					imagecopyresampled($t,$image,0,0,0,0,$z,$z,$size,$size);
					imagegif($t);
				break;
				case 'image/png':
					imagesavealpha($t, true);
					$trans_colour = imagecolorallocatealpha($t, 0, 0, 0, 127);
					imagefill($t, 0, 0, $trans_colour);
					imagecopyresampled($t,$image,0,0,0,0,$z,$z,$size,$size);
					imagepng($t,NULL,2); //lossless compression minimal (default is 6)
				break;
				case 'image/jpeg':
				case 'image/jpg':
				case 'image/pjpeg':
					imagecopyresampled($t,$image,0,0,0,0,$z,$z,$size,$size);
					imagejpeg($t,NULL,100); //100% quality (default is 75%)
				break;
			}
			$fr = ob_get_clean();
			$fh = fopen($image_temp,'w+');
			fwrite($fh,$fr);
			fclose($fh);
			unset($fr,$fh,$fc,$t);
			$res[] = $this->s3PutFile('image',$image_temp,'.'.$type2,$type);
		}
		return $res;
	}
	
	private function square_img($image,$width,$height)
	{
		if ($width > $height) {
			$square = $height;              // $square: square side length
			$offsetX = ($width - $height) / 2;  // x offset based on the rectangle
			$offsetY = 0;              // y offset based on the rectangle
		}
		// vertical rectangle
		elseif ($height > $width) {
			$square = $width;
			$offsetX = 0;
			$offsetY = ($height - $width) / 2;
		}
		$t = imagecreatetruecolor($square,$square);
		imagecopyresampled($t, $image, 0, 0, $offsetX, $offsetY, $square, $square, $square, $square);
		return [$square,$t];
	}
	
	//private methods
	private function img_upload($img_name,$files,$min_width = 120, $min_height = 120)
	{
		if(empty($files) || !isset($files[$img_name])){
			throw new \exception('You must select an image!');
		}
		if($files[$img_name]['error'])
		{
			throw new \exception($this->upload_errors($files[$img_name]['error']));
		}
		$image_temp = $files[$img_name]['tmp_name'];
		@$image_info = getimagesize($image_temp);
		$exif = [IMAGETYPE_GIF,IMAGETYPE_JPEG,IMAGETYPE_PNG];
		$finfo  = ['image/gif','image/png','image/jpeg','image/pjpeg'];
		$etype = exif_imagetype($image_temp);
		if(!function_exists('finfo_file')){
			throw new \exception('Unable to determine image mime type.');
		}
		$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE),$image_temp);
		if( !is_uploaded_file($files[$img_name]['tmp_name']) ){
			throw new \exception('bad file input');
		}
		if( !in_array($etype,$exif) || !in_array($type,$finfo)){
			throw new \exception('file type not allowed');
		}
		switch($type){
			case 'image/gif':
				$type2 = 'gif';
			break;
			case 'image/png':
				$type2 = 'png';
			break;
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				$type2 = 'jpg';
			break;
		}
		if( !$image_info ){
			throw new \exception('invalid image size.');
		}
		$width  = $image_info[0];
		$height = $image_info[1];
		if($width < $min_width || $height < $min_height){
			throw new \exception('Images must be atleast 120 x 120 in size.');
		}
		$fr = imagecreatefromstring(file_get_contents($image_temp));
		ob_start();
		switch($type){
			case 'image/gif':
				imagegif($fr);
			break;
			case 'image/png':
				imagesavealpha($fr, true);
				$trans_colour = imagecolorallocatealpha($fr, 0, 0, 0, 127);
				imagefill($fr, 0, 0, $trans_colour);
				imagepng($fr);
			break;
			case 'image/jpeg':
			case 'image/jpg':
			case 'image/pjpeg':
				imagejpeg($fr);
			break;
		}
		$fc = ob_get_clean();
		$fh = fopen($image_temp,'w+');
		fwrite($fh,$fc);
		fclose($fh);
		unset($fr,$fh,$fc);
		return $this->s3PutFile('image',$image_temp,'.'.$type2,$type);
	}
	
	private function mp3_upload($file_name,$files)
	{
		if(empty($files) || !isset($files[$file_name])){
			throw new \exception('You must select an mp3 file');
		}
		if($files[$file_name]['error'])
		{
			throw new \exception($this->upload_errors($files[$file_name]['error']));
		}
		if( !is_uploaded_file($files[$file_name]['tmp_name']) ){
			throw new \exception('bad file input');
		}
		
		$temp = $files[$file_name]['tmp_name'];
		
		if(!function_exists('finfo_file')){
			throw new \exception('Unable to determine image mime type.');
		}
		
		$finfo  = ['audio/mpeg','audio/mp3','audio/x-mpeg-3','audio/mpeg3','application/octet-stream'];
		$type = finfo_file(finfo_open(FILEINFO_MIME_TYPE),$temp);
		if(!in_array($type,$finfo)){
			throw new \exception('File type not allowed : '.$type);
		}
		return $this->s3PutFile('audio',$temp,'.mp3',$type);
	}

	private function upload_errors($err_code) {
		switch ($err_code) {
			case UPLOAD_ERR_INI_SIZE:
				return 'The uploaded file exceeds the upload_max_filesize directive in php.ini';
			case UPLOAD_ERR_FORM_SIZE:
				return 'The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form';
			case UPLOAD_ERR_PARTIAL:
				return 'The uploaded file was only partially uploaded';
			case UPLOAD_ERR_NO_FILE:
				return 'No file was uploaded';
			case UPLOAD_ERR_NO_TMP_DIR:
				return 'Missing a temporary folder';
			case UPLOAD_ERR_CANT_WRITE:
				return 'Failed to write file to disk';
			case UPLOAD_ERR_EXTENSION:
				return 'File upload stopped by extension';
			default:
				return 'Unknown upload error';
		}
	}
	
	
	private function s3DeleteFile($type,$key)
	{

		$config = (\StarterKit\App::getInstance())->aws_config;
		$s3 = new \Aws\S3\S3Client($config['config']);
		switch($type){
			case 'audio':
				$buckets = $config['mp3bucket'];
			break;
			case 'image':
				$buckets = $config['imgbucket'];
			break;
			case 'zip':
				$buckets = $config['zipbucket'];
			break;
		}
		if(!is_array($buckets)){
			$buckets = [$buckets];
		}
		foreach($buckets as $bucket){
			try{
				$result = $s3->deleteObject(array(
					'Bucket' => $bucket,
					'Key'    => $key
				)); 
			}
			catch(\exception $e){
				
			}
		}
	}
	
	private function s3PutFile($type,$tmp_file,$ext,$mime)
	{
		$config = (\StarterKit\App::getInstance())->aws_config;
		$s3 = new \Aws\S3\S3Client($config['config']);
		switch($type){
			case 'audio':
				$buckets = $config['mp3bucket'];
			break;
			case 'image':
				$buckets = $config['imgbucket'];
			break;
			case 'zip':
			
			break;
		}
		if(!is_array($buckets)){
			$buckets = [$buckets];
		}
		foreach($buckets as $bucket){
			try{
				$body = file_get_contents($tmp_file);
				$key = md5($body).'-'.time().$ext;
				//if it fails, i don't care. move on to the next bucket
				$s3->putObject([
					'Bucket'=>$bucket,
					'Key'=>$key,
					'Body'=>$body,
					'CacheControl'=>'max-age=172800',
					'Content-Type'=>$mime
				]);
				return 'http://'.$bucket.'.s3-website-us-east-1.amazonaws.com/'.$key; //if we were successful in writing to the bucket, we return the full url to the resource.
			}
			catch(\exception $e){
				
			}
		}
		// TODO: if we make it here, we know that all write attempts have failed.
		// we will return false, so caller knows to set the model to approved = 0, 
		// then we will call following which will pass this off to a 
		// job queue after moving tmp file to the new folder.
		// $this->s3DelayPutFile($body,$key);
		return false;
	}
	
	private function getTwig()
	{
		return new \Twig_Environment( new \Twig_Loader_Filesystem( (\StarterKit\App::getInstance())->twig_config['template_path'] ) );
	}
	
	private function url_safe($title)
	{
		$title = preg_replace('/[^A-Za-z 0-9]/','',$title);
		$title = preg_replace('/[\t\n\r\0\x0B]/', '', $title);
		$title = preg_replace('/([\s])\1+/', ' ', $title);
		$title = trim($title);
		$title = str_replace(' ','-',$title);
		return $title;
	}
	
	private function safe_name( $string )
	{
		return preg_replace(array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'), array('_', '.', ''), $string);
	}
}