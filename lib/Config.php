<?php
namespace Shantilab\YandexDirect;

use Bitrix\Main\IO\File;
use Bitrix\Main\IO\FileNotFoundException;
use Bitrix\Main\IO\InvalidPathException;
use Bitrix\Main\ArgumentNullException;
use Symfony\Component\Yaml\Yaml;

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
        $this->file = new File( __DIR__ . '/../settings.yml');
    }

    /**
     * @param null $type
     * @return array
     */
    public function getConfig($type = null)
    {
        if (!$this->file)
            throw new ArgumentNullException($this->file->getPath());
        if (!$this->file->isFile())
            throw new InvalidPathException($this->file->getPath());
        if (!$this->file->isExists())
            throw new FileNotFoundException($this->file->getPath());

        $params = Yaml::parse($this->file->getContents());

        if ($type){
            $ar = explode('\\', $type);
            foreach($ar as $val){
                $params = $params[$val];
            }
        }

        return $params;
    }
}