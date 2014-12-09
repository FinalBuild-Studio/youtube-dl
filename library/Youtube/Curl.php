<?php

namespace Youtube;

class Curl
{

    private   $ch    = null; 
    protected $proxy = null;

    protected function request($url = "")
    {
        $this->init();
        $this->setOption(CURLOPT_RETURNTRANSFER, true);
        $this->setOption(CURLOPT_URL, $url);

        $result = curl_exec($this->ch);
        $this->release();

        return $result;
    }

    protected function saveTo($url = "", $location = "")
    {
        $this->init();
        $fn = fopen($location, "w+");
        $this->setOption(CURLOPT_FILE, $fn);
        $this->setOption(CURLOPT_BINARYTRANSFER, true);
        $this->setOption(CURLOPT_URL, $url);
        curl_exec($this->ch);
        $this->release();
    }

    private function init()
    {
        $this->ch = curl_init();
        $this->setOption(CURLOPT_FOLLOWLOCATION, true);
        $this->setOption(CURLOPT_SSL_VERIFYPEER, false);
        $this->setOption(CURLOPT_SSL_VERIFYHOST, false);
        $this->setOption(CURLOPT_RETURNTRANSFER, true);

        if (isset($this->proxy)) {
            $this->setOption(CURLOPT_PROXY, $this->proxy);
        }
    }

    private function release()
    {
        @curl_close($this->ch);
    }

    private function setOption($option, $value)
    {
        curl_setopt($this->ch, $option, $value);
    }
}