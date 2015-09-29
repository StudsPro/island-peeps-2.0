<?php

require '../cron_db_connection.php';

$c = array(
  array('id' => '1','category' => 'Entrepreneur'),
  array('id' => '2','category' => 'Singer'),
  array('id' => '3','category' => 'Athletes'),
  array('id' => '4','category' => 'Actors'),
  array('id' => '5','category' => 'Politicians'),
  array('id' => '6','category' => 'Gangsters'),
  array('id' => '7','category' => 'Authors'),
  array('id' => '8','category' => 'Business'),
  array('id' => '9','category' => 'Model'),
  array('id' => '10','category' => 'Rapper'),
  array('id' => '11','category' => 'DJ'),
  array('id' => '12','category' => 'Radio Personality'),
  array('id' => '13','category' => 'Host'),
  array('id' => '14','category' => 'Selector, Sound Man'),
  array('id' => '15','category' => 'Comedian'),
  array('id' => '16','category' => 'Producer'),
  array('id' => '17','category' => 'Entertainer'),
  array('id' => '18','category' => 'Beauty Queen'),
  array('id' => '19','category' => 'Artist'),
  array('id' => '20','category' => 'Deejay'),
  array('id' => '21','category' => 'Journalist'),
  array('id' => '22','category' => 'Director'),
  array('id' => '23','category' => 'Security'),
  array('id' => '24','category' => 'Musician'),
  array('id' => '25','category' => 'Chief Usher'),
  array('id' => '26','category' => 'Dancer'),
  array('id' => '27','category' => 'Surgeon General'),
  array('id' => '28','category' => 'Religious Leader'),
  array('id' => '29','category' => 'Aviation'),
  array('id' => '30','category' => 'Writer'),
  array('id' => '31','category' => 'Adult Entertainment'),
  array('id' => '32','category' => 'Activist'),
  array('id' => '33','category' => 'Fashion Designer'),
  array('id' => '34','category' => 'Medical'),
  array('id' => '35','category' => 'Song writer'),
  array('id' => '36','category' => 'Filmmaker'),
  array('id' => '37','category' => 'Film Production'),
  array('id' => '38','category' => 'Law'),
  array('id' => '39','category' => 'TV Star'),
  array('id' => '40','category' => 'Photographer'),
  array('id' => '41','category' => 'Educator'),
  array('id' => '42','category' => 'Poet'),
  array('id' => '43','category' => 'Archivist'),
  array('id' => '44','category' => 'Murder Victim'),
  array('id' => '45','category' => 'Chief of Police'),
  array('id' => '46','category' => 'Actress'),
  array('id' => '47','category' => 'Weather Person'),
  array('id' => '48','category' => 'Terrorist'),
  array('id' => '49','category' => 'Equestrian'),
  array('id' => '50','category' => 'Blogger')
);

foreach($c as $row){
	$t = $db->model('category');
	$t->name = $row['category'];
	$db->store($t);
}
