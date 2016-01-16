<?php

namespace Shantilab\YandexDirect;

class Sender
{
    private $curlHandler;
    private $curlOptions = [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_AUTOREFERER    => true,
        CURLOPT_CONNECTTIMEOUT => 10,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_USERAGENT => 'Mozilla/5.0 (Windows NT 6.1; Win64; x64; rv:5.0) Gecko/20110619 Firefox/5.0',
        CURLOPT_TIMEOUT => 0,
        CURLOPT_POST => true,
    ];

    public function __construct($url)
    {
        $this->curlHandler = curl_init();
        curl_setopt_array($this->curlHandler, $this->curlOptions);
        curl_setopt($this->curlHandler, CURLOPT_URL, $url);

        return $this;
    }

    public function exec($data = [])
    {
        curl_setopt($this->curlHandler, CURLOPT_POSTFIELDS, $data);
        $result = curl_exec($this->curlHandler);
        if (curl_errno($this->curlHandler))
            throw new \Exception(curl_error($this->curlHandler));


        return (new Response($result));
    }
}