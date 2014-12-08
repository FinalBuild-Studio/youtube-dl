<?php

    require_once dirname(__FILE__) . "/library/autoload.php";

    if (count($argv) == 1) {
        echo "youtube-dl.php: missing operand\r\nTry `php youtube-dl.php -help' or `php youtube-dl.php -h' for more information.\r\n";
        exit();
    }

    foreach ($argv as $key => $value) {
        if ($key >= 1) {
            if ($value == "-h" || $value == "-help") {
                echo "Usage: php youtube-dl.php [OPTION]...\r\nyoutube-dl php version by michael34435\r\n\r\n-i, -id\t\tspecify youtube id\r\n-f, -format\tspecify youtube source format\r\n-p, -path\tsave file to this location\r\n\r\nplease report bugs to (michael34435@gmail.com)\r\n";
                exit();
            }
            putenv($value . (preg_match("/=/", $value) ? "" : "="));
        }
    }

    $format = getenv("-format");
    $path   = getenv("-path");
    $id     = getenv("-id");
    $id     = empty($id) ? getenv("-i") : $id;
    $path   = empty($path) ? getenv("-p") : $path;
    $format = empty($format) ? getenv("-f") : $format;
    $format = empty($format) ? "mp4" : $format;

    if (empty($id)) {
        exit("No yt id specified.");
    }

    if (empty($path)) {
        exit("No save path specified.");
    }

    $loader = new Youtube\Loader();
    $loader->visit($id)->getManifest()->getMedia($format)->save($path);
