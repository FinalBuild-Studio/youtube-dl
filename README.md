youtube-dl
==========

A downloader for PHP to download best quality youtube video.

##Usage 
<?php
	require_once dirname(__FILE__) . "/XML/xml2Array.php";
	require_once dirname(__FILE__) . "/Youtube/Curl.php";
	require_once dirname(__FILE__) . "/Youtube/Loader.php";

	$loader = new Youtube\Loader();
	$loader->visit("qQ4fiXa6Y74")->getManifest()->getMedia()->save("D:/");