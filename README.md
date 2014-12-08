youtube-dl
==========

A downloader for PHP to download best quality youtube video.

##Installation
1. Please unzip 7z file in ffmpeg first.  
Add new folder then download correspond [ffmpeg](https://www.ffmpeg.org/) if you use other platform(not windows, e.g. MacOSX).
2. Download [wfio](https://github.com/kenjiuno/php-wfio) system to support unicode if you use windows.

##Usage 
	<?php
		require_once dirname(__FILE__) . "/XML/xml2Array.php";
		require_once dirname(__FILE__) . "/Youtube/Curl.php";
		require_once dirname(__FILE__) . "/Youtube/Loader.php";

		$loader = new Youtube\Loader();
		$loader->visit("qQ4fiXa6Y74")->getManifest()->getMedia()->save("D:/");