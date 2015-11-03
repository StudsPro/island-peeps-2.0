<?php

error_reporting(-1);

define( 'LIB_PATH' , realpath( __DIR__ . '/../libs' ).'/'  );

require_once LIB_PATH . 'cron_db_connection.php';

$objRs = $db->model('social',1);


$twitter = json_decode($objRs->twitter);
$rss = json_decode($objRs->rss);
$facebook = json_decode($objRs->facebook);
$google = json_decode($objRs->google);
$instagram = json_decode($objRs->instagram);
$delicious = json_decode($objRs->delicious);
$vimeo = json_decode($objRs->vimeo);
$youtube = json_decode($objRs->youtube);
$pinterest = json_decode($objRs->pinterest);
$flickr = json_decode($objRs->flickr);
$dribbble = json_decode($objRs->dribbble);
$tumblr = json_decode($objRs->tumblr);
$stumbleupon = json_decode($objRs->stumbleupon);
$lastfm = json_decode($objRs->lastfm);
$deviantart = json_decode($objRs->deviantart);

if(isset($_GET['wall'])){
	$wall = 'true';
	$extra ='
	<script type="text/javascript" src="static/front/socialwall/js/jquery.social.stream.wall.1.6.js"></script>
	<link rel="stylesheet" type="text/css" href="static/front/socialwall/css/dcsns_wall.css" media="all" />
	<style>
	html {
		overflow: scroll;
	}
	body{
		padding:0;
		margin:0;
	}
	
	.dcsns-toolbar{
		padding: 14px 0px;
		position: fixed;
		top: 0px;
		left: 0px;
		right: 0px;
		z-index: 1;
		width: 100%;
		background: rgba(255,255,255,.3);
	}
	.dcsns{
		margin-top: 92px;
	}
	</style>
	';
}else{
	$wall = 'false';
	$extra = '
	<link rel="stylesheet" type="text/css" href="static/front/socialwall/css/dcsns_dark.css" media="all" />
	<style>
	html {
		overflow:scroll;
	}
	body{
		padding:0;
		margin:0;
	}
	.wrapper{
		padding:0!important;
		margin:0 !important;
		height:100vh !important;
	}
	.dcsns{
		height: 100vh;
		box-sizing: border-box;
	}
	.dcsns-content{
		height: calc(100vh - 55px) !important;
	}
	</style>
	';
}

?>
<!doctype html>
<html>
<head> 
<meta charset="utf-8" />
<title></title>


<script src="https://code.jquery.com/jquery-2.1.4.min.js"></script>
<script type="text/javascript" src="static/front/socialwall/js/jquery.social.stream.1.5.14.min.js"></script>
<?php echo $extra; ?>

<script type="text/javascript">
$(function(){
	$('#social-stream1').dcSocialStream({
		feeds: {


			twitter: {
				id: '<?php echo @$twitter->twitterid;?>',
				thumb: true,
				out: 'intro,text',
				search: '',
				retweets: true,
				replies: true,
			},

			facebook: {
				id: '<?php echo trim($facebook->fbid);?>',
				out: 'intro,thumb,user,text,share',
				text: 'content',
				comments: '',
				image_width: '<?php echo $facebook->image_width;?>',
			},

			rss: {
				id: '<?php echo @$rss->rssid;?>',
				out: '<?php echo @implode(",",$rss->out);?>',
				text: '<?php echo @$rss->text;?>',
			},
			stumbleupon: {
				id: '<?php echo @$stumbleupon->stumbleuponid;?>',
				out: '<?php echo @implode(",",$stumbleupon->out);?>',
				feed: '<?php echo @implode(",",$stumbleupon->feed);?>',
			},
			google: {
				id: '<?php echo $google->googleid;?>',
				api_key: '<?php echo $google->apikey;?>',
				out : '<?php echo implode(",",$google->out);?>',
				shares : <?php echo $google->shares;?>
			},
			delicious: {
				id: '<?php echo $delicious->deliciousid;?>',
				out: '<?php echo implode(",",$delicious->out);?>',
			},
			vimeo: {
				id: '<?php echo $vimeo->vimeoid;?>',
				out: '<?php echo implode(",",$vimeo->out);?>',
				feed: '<?php echo implode(",",$vimeo->feed);?>',
			},
			youtube: {
				id: '<?php echo $youtube->youtubeid;?>',
				out: '<?php echo implode(",",$youtube->out);?>',
				feed: '<?php echo implode(",",$youtube->feed);?>',
			},
			pinterest: {
				id: '<?php echo $pinterest->pinterestid;?>',
				out: '<?php echo implode(",",$pinterest->out);?>',
			},
			flickr: {
				id: '<?php echo $flickr->flickrid;?>',
				out: '<?php echo implode(",",$flickr->out);?>',
			},
			lastfm: {
				id: '<?php echo $lastfm->lastfmid;?>',
				out: '<?php echo implode(",",$lastfm->out);?>',
				feed: '<?php echo implode(",",$lastfm->feed);?>',
			},
			dribbble: {
				id: '<?php echo $dribbble->dribbbleid;?>',
				out: '<?php echo implode(",",$dribbble->out);?>',
				feed: '<?php echo implode(",",$dribbble->feed);?>',
			},
			tumblr: {
				id: '<?php echo $tumblr->tumblrid;?>',
				thumb: 250,
				out: '<?php echo implode(",",$tumblr->out);?>',
			},
			deviantart: {
				id: '<?php echo $deviantart->deviantartid;?>',
				out: '<?php echo implode(",",$deviantart->out);?>',
			},
 
			instagram: {
				id: '<?php echo $instagram->instagramid;?>',
				accessToken: '<?php echo $instagram->accessToken;?>',
				redirectUrl: 'http://localhost/mani/islandpeeps',
				clientId: '<?php echo $instagram->clientId;?>',
				comments: '<?php echo $instagram->comments;?>',
				likes: '<?php echo $instagram->like;?>',
				out: '<?php echo implode(",",$instagram->out);?>',
			},
 
		},
		rotate: {
			direction: '<?php echo trim($objRs->rotate_direction);?>',
			delay: '<?php echo trim($objRs->rotate_direction);?>'
		},
		twitterId: '<?php echo trim($twitter->twitterid);?>',
		control: false,
		filter: <?php echo trim($objRs->filter);?>,
		wall: <?php echo $wall; ?>,
		limit : <?php echo trim($objRs->limits);?>,
		days: <?php echo trim($objRs->days);?>,
		max: 'limit',
		order: '<?php echo trim($objRs->forder);?>',
		speed: <?php echo trim($objRs->speed);?>,
		iconPath: 'static/front/socialwall/images/dcsns-dark/',
		imagePath: 'static/front/socialwall/images/dcsns-dark/',
		center: true
	});/*HWD)$#k49eo*/ 
				 
});
</script>
</head>
<body>
	<div class="wrapper">
		<div id="social-stream1"></div>
		<div class="clear"></div>
	</div>
</body>
</html>