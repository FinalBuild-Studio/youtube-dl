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
                echo "Usage: php youtube-dl.php [OPTION]...\r\nyoutube-dl php version by michael34435\r\n\r\n-i, -id\t\tspecify youtube id\r\n-f, -format\tspecify youtube source format\r\n-p, -path\tsave file to this location\r\n-proxy\t\tallow proxy\r\n\r\nPlease report bugs to (michael34435@gmail.com).\r\n";
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
        exit("Can not find property media format. Please try `mp4' or `webm' instead.\r\n");
    }

    echo "Try downloading with curl ...", PHP_EOL;
    $loader->save($path);
