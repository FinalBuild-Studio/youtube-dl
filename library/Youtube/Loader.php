<?php

namespace Youtube;

class Loader extends Curl 
{

    private $data      = "";
    private $set       = array();
    private $audio     = "";
    private $video     = "";
    private $source    = array();
    private $baseUrl   = "https://www.youtube.com/watch?v=";
    private $title     = "";
    private $mediaType = "";

    public function setProxy($proxy)
    {
        $this->proxy = $proxy;
    }

    public function visit($id = "")
    {
        $this->data = $this->request($this->baseUrl . $id);
        $this->data = preg_replace("/(<head>)/", "$1<meta content='text/html; charset=utf-8' http-equiv='content-type'>", $this->data);

        return $this;
    }

    public function getManifest()
    {
        if (!empty($this->data)) {
            $dom         = new \DOMDocument;
            @$dom->loadHTML($this->data);
            $scripts     = $dom->getElementsByTagName("script");
            $this->title = $dom->getElementById("eow-title")->nodeValue;
            $this->title = trim($this->title);
            $this->title = preg_replace("/\\\|\/|\"|\:|\?|\||\*|\>|\</", "_", $this->title);
            foreach ($scripts as $script) {
                if (preg_match("/var\s+ytplayer/", $script->nodeValue)) {
                    $config = $script->nodeValue;
                    preg_match("/\"dashmpd\"\s*\:\s*\"([\"\w\\\\\/\-\.\:\%]*)\"\s*,\s*\"\w+\"/", $config, $match);
                    if (isset($match[1])) {
                        $manifestUrl = preg_replace("/\\\/", "", $match[1]);
                        $manifest    = $this->request($manifestUrl);
                        $parser      = new \XML\xml2Array();
                        $output      = $parser->parse($manifest);
                        $this->set   = $output[0]["children"][0]["children"];

                        return $this;
                    }
                }
            }
        }

        return false;
    }

    public function getMedia($mediaType = "mp4")
    {
        $max = array();
        if (!empty($this->set)) {
            $this->mediaType = $mediaType;
            foreach ($this->set as $setKey => $setValue) {
                if ($setValue["name"] == "ADAPTATIONSET") {
                    $type = "";
                    foreach ($setValue["attrs"] as $attrsKey => $attrsValue) {
                        if ($attrsKey == "MIMETYPE") {
                            $type = $attrsValue;
                            break;
                        }
                    }

                    if (preg_match("/\/" . quotemeta($mediaType) . "$/", $type)) {
                        $media       = explode("/", $type);
                        $media       = reset($media);
                        $max[$media] = isset($max[$media]) ? $max[$media] : 0;
                        $mediaSelect = $setValue["children"];
                        foreach ($mediaSelect as $mediaSelectKey => $mediaSelectValue) {
                            foreach ($mediaSelectValue["attrs"] as $mediaSelectValueKey => $mediaSelectValueSubValue) {
                                if ($mediaSelectValueKey == "BANDWIDTH") {
                                    if (intval($mediaSelectValueSubValue) > $max[$media]) {
                                        $max[$media] = intval($mediaSelectValueSubValue);
                                        foreach ($mediaSelectValue["children"] as $mediaSelectChildrenKey => $mediaSelectChildrenValue) {
                                            if ($mediaSelectChildrenValue["name"] == "BASEURL") {
                                                $this->source[$media] = $mediaSelectChildrenValue["tagData"];
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return $this;
        }

        return false;
    }

    public function save($location = "", $save = "")
    {
        if (!empty($this->source)) {
            $path      = dirname(__FILE__) . "/../../ffmpeg";
            $path      = realpath($path);
            $os        = substr(PHP_OS, 0, 3);
            $path      = $path . "/{$os}/bin/ffmpeg" . ($os == "WIN" ? ".exe" : "");
            $path      = realpath($path);
            $cache     = dirname(__FILE__) . "/../../cache";
            $cache     = realpath($cache);
            $location  = realpath($location);
            if (empty($save)) {
                $audio = $cache . "/" . uniqid(null, true);
                $video = $cache . "/" . uniqid(null, true);
                $this->saveTo($this->source["audio"], $audio);
                $this->saveTo($this->source["video"], $video);
                $audio     = realpath($audio);
                $video     = realpath($video);
                $tempfile  = uniqid(null, true) . "." . $this->mediaType;
                $finalPath = preg_replace("/(\\\|\/)+/", ($os == "WIN" ? "\\" : "/"), $location . "/" . $this->title . "." . $this->mediaType);
                $tempDest  = preg_replace("/(\\\|\/)+/", ($os == "WIN" ? "\\" : "/"), $location . "/" . $tempfile);
                $tempPath  = preg_replace("/(\\\|\/)+/", ($os == "WIN" ? "\\" : "/"), $cache . "/" . $tempfile);
                
                // ffmpeg -i video.mp4 -i audio.wav \
                // -c:v copy -c:a aac -strict experimental \
                // -map 0:v:0 -map 1:a:0 output.mp4
                $command   = "\"{$path}\" -y -i \"{$video}\" -i \"{$audio}\" " .
                            "-c:v copy -c:a aac -strict experimental -map 0:v:0 -map 1:a:0 " .
                            "\"{$tempPath}\" && ". ($os == "WIN" ? "MOVE /Y" : "mv -f") .
                            " \"{$tempPath}\" \"{$location}\"";
            } elseif (isset($this->source[$save])) {
                $tempfile  = $cache . "/" . uniqid(null, true);
                $this->saveTo($this->source[$save], $tempfile);
                $fakeFile  = uniqid(null, true) . "." . ($save == "video" ? $this->mediaType : "mp3");
                $tempDest  = preg_replace("/(\\\|\/)+/", ($os == "WIN" ? "\\" : "/"), $location . "/" . $fakeFile);
                $tempPath  = preg_replace("/(\\\|\/)+/", ($os == "WIN" ? "\\" : "/"), $cache . "/" . $fakeFile);
                $finalPath = preg_replace("/(\\\|\/)+/", ($os == "WIN" ? "\\" : "/"), $location . "/" . $this->title . "." . ($save == "video" ? $this->mediaType : "mp3"));
                $command   = "\"{$path}\" -y -i \"{$tempfile}\" \"{$tempPath}\" && " . ($os == "WIN" ? "MOVE /Y" : "mv -f") . " \"{$tempPath}\" \"{$location}\"";
                echo $command;
            } else {
                exit("Please assign `audio' or `video'.");
            }

            exec($command);
            $wfio = ($os == "WIN" ? "wfio://" : "");

            if (is_file($wfio . $finalPath)) {
                @unlink($wfio . $finalPath);
            }

            @rename($wfio . $tempDest, $wfio . $finalPath);
            @unlink($wfio . $audio);
            @unlink($wfio . $video);
            @unlink($wfio . $tempfile);
        }

        return false;
    }
}