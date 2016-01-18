<?php

namespace Shantilab\YandexDirect;

use \Bitrix\Main\Web\Json;

/**
 * Class Response
 * @package Shantilab\YandexDirect
 */
class Response implements \ArrayAccess
{
    /**
     * @var
     */
    private $response;
    /**
     * @var array|bool|mixed|string
     */
    private $fields;
    /**
     * @var string
     */
    private $state;
    /**
     * @var array
     */
    private $errors = [];

    /**
     * Response constructor.
     * @param $response
     */
    public function __construct($response)
    {
        $this->response = $response;
        $this->fields = Json::decode($this->response);

        if ($this->fields['error_code'])
           $this->setError($this->fields);

        $this->state = 'ok';

        if ($this->getErrors())
            $this->state = 'error';
    }

    /**
     * @return array|bool|mixed|string
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * @param array $error
     */
    private function setError($error = [])
    {
        $this->errors[] = [
            'code' => $error['error_code'],
            'string' => $error['error_str'],
            'detail' => $error['error_detail'],
        ];
    }

    /**
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->fields[$offset]);
    }

    /**
     * @param mixed $offset
     * @return null
     */
    public function offsetGet($offset)
    {
        return isset($this->fields[$offset]) ? $this->fields[$offset] : null;
    }

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->fields[] = $value;
        } else {
            $this->fields[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->fields[$offset]);
    }
}