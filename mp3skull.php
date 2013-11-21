#!/usr/bin/php
<?php
include 'lib/sunny.php';
include 'crawlers/mp3skull.php';

$site = 'http://mp3skull.com/';
$table = 'mp3skull';

$append = false;
$threads = 10;
$limit = 100;

$ignore = array(
			'4shared\.com',
		);

/* create a new sunny crawler */
$sunny = new Mp3skull($site, $db);

/* append_queue autostarts crawl on init / autoqueues link on crawl */
	$sunny->append_queue($append);

/* set of patterns to ignore in the found url */
	$sunny->ignore_url_pattern($ignore);

/* uncomment this section to create a new table */
	$sunny->init($table, $threads, $limit);

/* uncomment this section to start the crawler */
	// $sunny->crawl($table, $threads, $limit);
	$sunny->crawl_all($table, $threads, $limit);

?>
