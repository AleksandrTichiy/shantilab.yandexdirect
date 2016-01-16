<?php

namespace Shantilab\YandexDirect;

use \Bitrix\Main\Web\Json;

class Response
{
    private $response;
    private $fields;
    private $state;

    public function __construct($response)
    {
        $this->response = $response;
        $this->fields = Json::decode($response);
    }

    public function getFields()
    {
        return $this->fields;
    }
}