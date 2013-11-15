#!/usr/bin/php
<?php
include 'lib/sunny.php';
include 'plugins/mp3rehab.com.php';

$spider = new Mp3rehab('http://mp3rehab.com', $db);

/* configure crawler targets */

/* targets the download link */
$spider->set_download_link('.dllink a');
/* targets for meta info */
$spider->set_meta_info('.infotext a');

$spider->init();
# $spider->resume(1);