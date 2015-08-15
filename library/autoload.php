<?php

function loadFolder($folder = "")
{
    if (empty($folder)) {
        $folder = dirname(__FILE__) . "/" . $folder;
    }
    $data = glob($folder . "/*");
    foreach ($data as $file) {
        $file = realpath($file);
        $ext  = explode(".", $file);
        $ext  = end($ext);
        if (is_file($file) && $ext == "php" && $file != __FILE__) {
            require_once $file;
        } elseif (is_dir($file)) {
            loadFolder($file);
        }
    }
}

loadFolder();
