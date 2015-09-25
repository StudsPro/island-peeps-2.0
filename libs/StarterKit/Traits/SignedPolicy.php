<?php

namespace StarterKit\Traits;

trait SignedPolicy
{

	private function sign_url($content) 
	{
		
		$cf_url = 'http://d1kgxk23qvdu1d.cloudfront.net/';
		$app = \StarterKit\App::getInstance();

		
		$policy = [
			'Statement'=>[
				'Resource'=>$cf_url.$content,
				'Condition'=>[
					'IpAddress'=>[
						'AWS:SourceIp'=>$app->remote_addr . '/32',
					],
					'DateLessThan'=>[
						'AWS:EpochTime'=>time() + 1500
					]
				]
			]
		];
		
		$policy = json_encode($policy);
		
		$client = new \Aws\CloudFront\CloudFrontClient($app->aws_config['config']);
		
		$url = $client->getSignedUrl([
			'url'=>$cf_url.$content,
			'policy'=>$policy,
			'key_pair_id'=>$app->key_id,
			'private_key'=>$app->key_file,
			'expires'=>time() + 1500
		]);

		return $url;
    }

}