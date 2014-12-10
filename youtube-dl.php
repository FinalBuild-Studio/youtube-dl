<?php

    require_once dirname(__FILE__) . "/library/autoload.php";

    if (count($argv) == 1) {
        echo "youtube-dl.php: missing operand\r\nTry `php youtube-dl.php -help' or `php youtube-dl.php -h' for more information.\r\n";
        exit();
    }

    $concat = false;
    $last   = "";
    foreach ($argv as $key => $value) {
        if ($key >= 1) {
            if ($value == "-h" || $value == "-help") {
                echo "Usage: php youtube-dl.php [OPTION]...\r\n" .
                     "youtube-dl php version by michael34435\r\n" .
                     "\r\n-i, -id\t\tSpecify youtube id" .
                     "\r\n-f, -format\tSpecify youtube source format" .
                     "\r\n-p, -path\tSave file to this location" .
                     "\r\n-s, -save\tOnly save specified format, two values `audio' or `video'. \r\n" .
                     "\t\tSelect `Audio' will save as mp3 file." .
                     "\r\n-proxy\t\tAllow proxy\r\n" .
                     "\r\nPlease report bugs to (michael34435@gmail.com).\r\n";
                exit();
            }

            if ($concat) {
                $concat = false;
                if (!preg_match("/^-/", $value)) {
                    putenv($last . "=" . $value);
                }
            }

            if (preg_match("/^-/", $value)) {
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
    $id     = empty($id) ? getenv("-i") : $id;
    $path   = empty($path) ? getenv("-p") : $path;
    $format = empty($format) ? getenv("-f") : $format;
    $save   = empty($save) ? getenv("-s") : $save;
    $format = empty($format) ? "mp4" : $format;

    if (empty($id)) {
        exit("No yt id specified.\r\n");
    }

    if (empty($path)) {
        exit("No save path specified.\r\n");
    }

    $loader = new Youtube\Loader();
    
    if (!empty($proxy)) {
        $loader->setProxy($proxy);   
    }

    $loader = $loader->visit($id);

    echo "Analyzing available media manifest ...", PHP_EOL;
    if (!($loader = @$loader->getManifest())) {
        exit("Download failed, please add proxy setting or retry again.\r\n");
    }


    echo "Analyzing best media type ...", PHP_EOL;
    if (!($loader = $loader->getMedia($format))) {
        exit("Can not find proper media format. Please try `mp4' or `webm' instead.\r\n");
    }

    echo "Try downloading with curl ...", PHP_EOL;
    $loader->save($path, $save);
