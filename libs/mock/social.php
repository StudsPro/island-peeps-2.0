<?php

require '../cron_db_connection.php';

$d= array('id' => '1','twitter' => '{"twitterid":"Island_Peeps","retweets":"true","replies":"true"}','rss' => '{"rssid":"","out":["intro","title","text","share"],"text":"content"}','stumbleupon' => '{"stumbleuponid":"","out":["intro","title","thumb","user","text","share"],"feed":["favorites","reviews"]}','facebook' => '{"fbid":"565393113519119","out":["intro","title","user","text","share"],"text":"content","comments":"","image_width":"3"}','google' => '{"googleid":"116083955392713125918","out":["intro","title","thumb","text","share"],"apikey":"AIzaSyBz1YBMlqRJp9tsBrFgoaZxJcYYGz75AEg","shares":"true"}','instagram' => '{"instagramid":"!547541164,#mixtape,","out":["intro","thumb","meta","user","text","share"],"accessToken":"547541164.af29da7.7165004fa1b54aef89688062b068f4f8","clientId":"af29da7ce6e54b628a9cc31806a55403","comments":"3","like":"3"}','delicious' => '{"deliciousid":"","out":["intro","title","thumb","user","text","share"]}','vimeo' => '{"vimeoid":"","feed":["likes","videos","appears_in","all_videos","albums","channels","groups"],"out":["intro","title","thumb","user","text","share"]}','youtube' => '{"youtubeid":"","feed":["uploads","favorites","newsubscriptionvideos"],"out":["intro","title","thumb","text","share"]}','pinterest' => '{"pinterestid":"islandpeeps","out":["intro","title","thumb","user","text","share"]}','flickr' => '{"flickrid":"","out":["intro","title","thumb","text","share"]}','lastfm' => '{"lastfmid":"","out":["intro","title","thumb","user","text","share"],"feed":["recenttracks","lovedtracks","replytracker"]}','dribbble' => '{"dribbbleid":"","out":["intro","title","thumb","user","text","share"],"feed":["shots","likes"]}','deviantart' => '{"deviantartid":"","out":["intro","title","thumb","user","text","share"]}','tumblr' => '{"tumblrid":"islandpeeps","out":["intro","title","user","text","share"]}','limits' => '99','days' => '99','fmax' => 'days','speed' => '600','forder' => 'date','filter' => 'true','rotate_direction' => 'down','rotate_delay' => '2000');

unset($d['id']);

$t = $db->model('social');

foreach($d as $k=>$v)
{
	$t->{$k} = $v;
}

$db->store($t);

