<?php

namespace Youtube;

class Loader extends Curl 
{

    private $data       = "";
    private $set        = array();
    private $audio      = "";
    private $video      = "";
    private $source     = array();
    private $baseUrl    = "https://www.youtube.com/watch?v=";
    private $title      = "";
    private $mediaType  = "";
    private $exceptType = array("webm" => "mkv");

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
                    preg_match("/\"dashmpd\"\s*\:\s*\"([\"\w\\\\\/\-\.\:\%]*)\"\s*(,\s*\"\w+\"|\s*\}\s*)/", $config, $match);
                    if (isset($match[1])) {
                        $manifestUrl = preg_replace("/\\\/", "", $match[1]);
                        if (preg_match("/\/s\/([a-zA-Z0-9\.]*)\//", $manifestUrl, $signature)) {
                            foreach ($scripts as $scriptTemp) {
                                preg_match("/\"js\"\s*\:\s*\"([a-zA-Z_0-9\.\/\\\-]*)\"\s*(,\s*\"\w+\"|\s*\}\s*)/", $config, $src);
                                if (isset($src[1])) {
                                    $src         = preg_replace("/\\\/", "", $src[1]);
                                    $src         = preg_match("/^\/\//", $src) ? "http:" . $src : $src;
                                    $html5player = $this->request($src);
                                    $signature   = $signature[1];
                                    $signature   = trim($signature);
                                    $manifestUrl = $manifestUrl . "/signature/" . $this->decryptSignature($signature, $html5player);

                                    break;
                                }
                            }
                        }
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
            $this->mediaType = $this->getMediaType($mediaType);
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
                $finalPath = preg_replace("/(\\\|\/)+/", DIRECTORY_SEPARATOR, $location . "/" . $this->title . "." . $this->mediaType);
                $tempDest  = preg_replace("/(\\\|\/)+/", DIRECTORY_SEPARATOR, $location . "/" . $tempfile);
                $tempPath  = preg_replace("/(\\\|\/)+/", DIRECTORY_SEPARATOR, $cache . "/" . $tempfile);
                
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
                $tempDest  = preg_replace("/(\\\|\/)+/", DIRECTORY_SEPARATOR, $location . "/" . $fakeFile);
                $tempPath  = preg_replace("/(\\\|\/)+/", DIRECTORY_SEPARATOR, $cache . "/" . $fakeFile);
                $finalPath = preg_replace("/(\\\|\/)+/", DIRECTORY_SEPARATOR, $location . "/" . $this->title . "." . ($save == "video" ? $this->mediaType : "mp3"));
                $command   = "\"{$path}\" -y -i \"{$tempfile}\" \"{$tempPath}\" && " . ($os == "WIN" ? "MOVE /Y" : "mv -f") . " \"{$tempPath}\" \"{$location}\"";
            } else {
                exit("Please assign `audio' or `video'.\r\n");
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

    private function getMediaType($type)
    {
        if (isset($this->exceptType[$type])) {
            return $this->exceptType[$type];
        }

        return $type;
    }

    private function decode($sig, $arr)
    {
        if (preg_match("/^(\-|)\d+$/", $sig)) return null;
        $sigA = str_split(strval($sig));
        for ($i = 0; $i < count($arr); $i ++) {
            $act = $arr[$i];
            if (!preg_match("/^(\-|)\d+$/", $act)) {
                return null;
            }

            $sigA = ($act > 0) ? $this->swap($sigA, $act) : (($act == 0) ? array_reverse($sigA) : array_slice($sigA, -$act));
        }

        $result = join($sigA, '');
        return $result;
    }

    private function swap($a, $b)
    {
        $c = $a[0];
        $a[0] = $a[$b % count($a)];
        $a[$b] = $c;
        return $a;
    }


    private function decryptSignature($sig, $code)
    {
        if ($sig == null) return '';    

        $arr = $this->fetchSig($code);

        if ($arr) {
            $sig2 = $this->decode($sig, $arr);
            if ($sig2) return $sig2;
        }
    
        return $sig; 
    }

    private function fetchSig($code)
    {
        preg_match("/\.set\s*\(\"signature\"\s*,\s*([a-zA-Z0-9_$][\w$]*)\(/", $code, $sigMatch)
            || preg_match("/\.sig\s*\|\|\s*([a-zA-Z0-9_$][\w$]*)\(/", $code, $sigMatch)
            || preg_match("/\.signature\s*=\s*([a-zA-Z_$][\w$]*)\([a-zA-Z_$][\w$]*\)/", $code, $sigMatch); //old

        // get signature function name
        $sigFunctionName = str_replace('$', '\\$', $sigMatch[1]);

        // get function content
        preg_match('/function \\s*' . $sigFunctionName .
    '\\s*\\([\\w$]*\\)\\s*\\{[\\w$]*=[\\w$]*\\.split\\(""\\);(.+);return [\\w$]*\\.join/', $code, $sigCodeMatch);

        // get reserve function name
        preg_match("/([\w$]*)\s*:\s*function\s*\(\s*[\w$]*\s*\)\s*\{\s*(?:return\s*)?[\w$]*\.reverse\s*\(\s*\)\s*\}/", $code, $reverseMatch);
        $reverseFunctionName = str_replace('$', '\\$', $reverseMatch[1]);

        // get slice function name
        preg_match("/([\w$]*)\s*:\s*function\s*\(\s*[\w$]*\s*,\s*[\w$]*\s*\)\s*\{\s*(?:return\s*)?[\w$]*\.(?:slice|splice)\(.+\)\s*\}/", $code, $sliceMatch);
        $sliceFunctionName = str_replace('$', '\\$', $sliceMatch[1]);

        // tools
        $regSlice = '/\\.(?:' . 'slice' . ($sliceFunctionName ? '|' . $sliceFunctionName : '') . 
    ')\\s*\\(\\s*(?:[a-zA-Z_$][\\w$]*\\s*,)?\\s*([0-9]+)\\s*\\)/';
        $regReverse = '/\\.(?:' . 'reverse' . ($reverseFunctionName ? '|' . $reverseFunctionName : '') . 
    ')\\s*\\([^\\)]*\\)/';
        $regSwap = "/[\w$]+\s*\(\s*[\w$]+\s*,\s*([0-9]+)\s*\)/";
        $regInline = "/[\w$]+\[0\]\s*=\s*[\w$]+\[([0-9]+)\s*%\s*[\w$]+\.length\]/";

        $codePieces = explode(";", $sigCodeMatch[1]);
        $decodeArray = array();
        for ($key = 0; $key < count($codePieces); $key++) { 
            $piece = $codePieces[$key];
            $piece = trim($piece);
            if (count($piece) > 0) {
                preg_match($regSlice, $piece, $arrSlice);
                preg_match($regReverse, $piece, $arrReverse);

                if ($arrSlice && count($arrSlice) >= 2) {
                    $slice = intval($arrSlice[1], 10);
                    if (preg_match("/^(\-|)\d+$/", $slice)) {
                        $decodeArray[] = -$slice;
                    } else {
                        // return '';
                    }
                } elseif ($arrReverse && count($arrReverse) >= 1) { // reverse
                    $decodeArray[] = 0;
                } elseif ($check = strpos($piece, '[0]') && $check >= 0) { // inline swap
                    if ($key + 2 < count($codePieces) &&
                        ($check = strpos($codePieces[$key + 1], '.length') && $check >= 0) &&
                        ($check = strpos($codePieces[$key + 1], '[0]') && $check >= 0)>= 0) {
                        preg_match($regInline, $codePieces[$key + 1], $inline);
                        $inline = isset($inline[1]) ? $inline[1] : null;
                        $inline = intval($inline, 10);
                        $decodeArray[] = $inline;
                        $key += 2;
                    } else {
                        // return '';  
                    }
                } elseif ($check = strpos($piece, ',') && $check >= 0) { // swap
                    preg_match($regSwap, $piece, $swap);
                    $swap = isset($swap[1]) ? $swap[1] : null;
                    $swap = intval($swap, 10);
                    if (preg_match("/^(\-|)\d+$/", $swap) && $swap > 0){
                        $decodeArray[] = $swap;
                    } else {
                        // return '';
                    }
                } else {
                    // return '';
                }
            }
        }

        return $decodeArray;
    }
}