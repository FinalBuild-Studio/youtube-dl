<?php

namespace Youtube;

class Curl
{

    private $ch = null; 
    
    protected function __construct()
    {
        $this->ch = curl_init();
        $this->init();
    }

    protected function request($url = "")
    {
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_URL, $url);

        return curl_exec($this->ch);
    }

    protected function saveTo($url = "", $location = "")
    {
        $fn = fopen($location, "w+");
        $this->setOption(CURLOPT_FILE, $fn);
        $this->setOption(CURLOPT_BINARYTRANSFER, true);
        $this->setOption(CURLOPT_URL, $url);

        return curl_exec($this->ch);
    }

    protected function __destruct()
    {
        curl_close($this->ch);
    }

    private function init()
    {
        $this->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
    }

    private function setOption($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
    }
}