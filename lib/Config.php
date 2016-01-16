<?php
namespace Shantilab\YandexDirect;

use \Symfony\Component\Yaml\Yaml;

/**
 * Class Config
 * @package Shantilab\YandexApi
 */
class Config
{
    /**
     * @var string
     */
    protected $file = '';

    /**
     * Config constructor.
     */
    public function __construct()
    {
        $this->file = __DIR__ . '/../settings.yml';
    }

    /**
     * @param null $type
     * @return array
     */
    public function getConfig($type = null)
    {
        $params = Yaml::parse(file_get_contents($this->file));

        if ($type){
            $ar = explode('\\', $type);
            foreach($ar as $val){
                $params = $params[$val];
            }
        }

        return $params;
    }
}