#!/usr/bin/php
<?php
@ob_end_clean();
error_reporting(0);
require_once dirname(__FILE__) . "/library/autoload.php";
$accept = ["-i", "-id", "-f", "-format", "-p", "-path", "-s", "-save", "-proxy", "-height", "-l", "-list"];
if (count($argv) === 1) {
    echo "youtube-dl.php: missing operand.\r\n" .
         "Try `php youtube-dl.php -help' or `php youtube-dl.php -h' for more information.\r\n";
    exit();
}

$listYoutube = false;
$concat      = false;
$last        = "";
foreach ($argv as $key => $value) {
    if ($key >= 1) {
        if ($value === "-h" || $value === "-help") {
            echo "Usage: php youtube-dl.php [OPTION]...\r\n" .
                 "youtube-dl php version by michael34435\r\n" .
                 "\r\n-i, -id\t\tSpecify youtube id" .
                 "\r\n-f, -format\tSpecify youtube source format" .
                 "\r\n-p, -path\tSave file to this location" .
                 "\r\n-l, -list\tList this youtube video height." .
                 "\r\n-s, -save\tOnly save specified format, two values `audio' or `video'. \r\n" .
                 "\t\tSelect `audio' will save as mp3 file." .
                 "\r\n-height\t\tSave file with specified height.(Please check with option -l)" .
                 "\r\n-proxy\t\tAllow proxy\r\n" .
                 "\r\nPlease report bugs to (michael34435gmail.com).\r\n";
            exit();
        }
        if ($value === "-l" || $value === "-list") {
            $listYoutube = true;
        }
        if ($concat) {
            $concat = false;
            if (!in_array($value, $accept)) {
                putenv($last . "=" . $value);
            }
        }
        if (in_array($value, $accept)) {
            $concat = true;
            $last   = $value;
        }
    }
}

$format = getenv("-format");
$path   = getenv("-path");
$id     = getenv("-id");
$proxy  = getenv("-proxy");
$save   = getenv("-save");
$height = getenv("-height");
$id     = empty($id) ? getenv("-i") : $id;
$path   = empty($path) ? getenv("-p") : $path;
$format = empty($format) ? getenv("-f") : $format;
$save   = empty($save) ? getenv("-s") : $save;
$format = empty($format) ? "mp4" : $format;
if (empty($id)) {
    exit("No yt id specified.\r\n");
}

if (!$listYoutube && empty($path)) {
    exit("No save path specified.\r\n");
}

$loader = new Youtube\Loader();
if (!empty($proxy)) {
    echo "Setting youtube proxy ...", PHP_EOL;
    $loader->setProxy($proxy);
}

$loader = $loader->visit($id);
echo "Analyzing available media manifest ...", PHP_EOL;
if (!($loader = $loader->getManifest())) {
    exit("Download failed, please add proxy setting or retry again.\r\n");
}

if ($listYoutube) {
    $loader->getReturnHeight();
} else {
    if (!empty($height)) {
        echo "Setting video height ...", PHP_EOL;
        $loader->setHeight($height);
    }
}

echo "Analyzing media type ...", PHP_EOL;
if (!($loader = $loader->getMedia($format))) {
    exit("Can not find proper media format. Please try `mp4' or `webm' instead.\r\n");
}

if (is_array($loader)) {
    echo "Available height:", PHP_EOL;
    foreach ($loader as $height) {
        echo $height . "pixel", PHP_EOL;
    }
    exit();
}
echo "Try downloading with curl ...", PHP_EOL;
$loader->save($path, $save);
