<?php
namespace Shantilab\YandexDirect\Exceptions;

use Bitrix\Main\SystemException;

/**
 * Class SettingParameterNullException
 * @package Shantilab\YandexDirect\Exceptions
 */
class SettingParameterNullException extends SystemException
{
    /**
     * SettingParameterNullException constructor.
     * @param string $parameter
     */
    public function __construct($parameter)
    {
        parent::__construct(sprintf(
            'Argument \'%s\' is null or empty',
            $parameter
        ));
    }
}