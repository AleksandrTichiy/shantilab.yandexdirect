<?php

namespace Shantilab\YandexDirect;

use Bitrix\Main\Entity;
use Bitrix\Main\Type;
use Jwt\Jwt;
use Jwt\Algorithm\HS256Algorithm;
use Shantilab\YandexDirect\Exceptions\SettingParameterNullException;

/**
 * Class AccountsTable
 * @package Shantilab\YandexDirect
 */
class AccountsTable extends Entity\Datamanager
{
    /**
     * @return string
     */
    public static function getTableName()
    {
        return 'shantilab_yandexdirect_accounts';
    }

    /**
     * @return string
     */
    public static function getConnectionName()
    {
        return 'default';
    }

    /**
     * @return array
     */
    public static function getMap()
    {
        return [
            
            new Entity\IntegerField('ID', [
                'primary' => true,
                'autocomplete' => true
            ]),

            new Entity\DatetimeField('CREATE_DATE', [
                'required' => true,
                'default_value' => new Type\DateTime
            ]),

            new Entity\DatetimeField('UPDATE_DATE', [
                'required' => true,
                'default_value' => new Type\DateTime
            ]),

            new Entity\IntegerField('USER_ID', [
                'required' => true
            ]),

            new Entity\StringField('LOGIN', [
                'required' => true
            ]),

            new Entity\StringField('ACCESS_TOKEN', [
                'required' => true,
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return self::encodeVal($value);
                        }
                    ];
                },
                'fetch_data_modification' => function () {
                    return [
                        function ($value) {
                            return self::decodeVal($value);
                        }
                    ];
                }
            ]),

            new Entity\IntegerField('TOKEN_EXPIRES_IN', [
                'required' => true
            ]),

            new Entity\StringField('TOKEN_TYPE'),

            new Entity\DatetimeField('TOKEN_FINAL_DATE', [
                'required' => true,
            ]),

            new Entity\StringField('MASTER_TOKEN', [
                'save_data_modification' => function () {
                    return [
                        function ($value) {
                            return self::encodeVal($value);
                        }
                    ];
                },
                'fetch_data_modification' => function () {
                    return [
                        function ($value) {
                            return self::decodeVal($value);
                        }
                    ];
                }
            ]),

            new Entity\IntegerField('FINANCE_NUM', [
                'default_value' => 0
            ]),
        ];
    }

    /**
     * @param Entity\Event $event
     * @return Entity\EventResult
     */
    public static function onBeforeUpdate(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $data = $event->getParameter('fields');
        $fields = [];

        if (isset($data['TOKEN_EXPIRES_IN']) && !self::tokenAlreadyExists($data)){
            $expires = intval($data['TOKEN_EXPIRES_IN']);
            $dateTime = new Type\DateTime();
            $dateTime->add('+ ' . $expires . ' sec');
            $fields['TOKEN_FINAL_DATE'] = $dateTime;
        }

        $fields['UPDATE_DATE'] = new Type\DateTime;

        $result->modifyFields($fields);

        return $result;
    }

    /**
     * @param Entity\Event $event
     * @return Entity\EventResult
     */
    public static function onBeforeAdd(Entity\Event $event)
    {
        $result = new Entity\EventResult;
        $data = $event->getParameter('fields');

        if (isset($data['TOKEN_EXPIRES_IN'])){
            $dateTime = new Type\DateTime();
            $dateTime->add('+ ' . $data['TOKEN_EXPIRES_IN'] . ' sec');
            $result->modifyFields(['TOKEN_FINAL_DATE' => $dateTime]);
        }

        return $result;
    }

    /**
     * @param $data
     * @return bool
     */
    public static function tokenAlreadyExists($data){
        if (isset($data['USER_ID'], $data['LOGIN'], $data['ACCESS_TOKEN'])){
            $row = self::getRow([
                'filter' => [
                    'USER_ID' => $data['USER_ID'],
                    'LOGIN' => $data['LOGIN'],
                    'ACCESS_TOKEN' => $data['ACCESS_TOKEN']
                ]
            ]);

            if ($row) return true;
        }

        return false;
    }

    /**
     * @param $value
     * @return string
     * @throws SettingParameterNullException
     */
    public static function encodeVal($value)
    {
        $value = Jwt::encode($value, ['algorithm' =>  new HS256Algorithm(self::getSecretKey())]);
        return $value;
    }

    /**
     * @param $value
     * @return null
     * @throws SettingParameterNullException
     * @throws \Jwt\Exception\SignatureInvalidException
     */
    public static function decodeVal($value)
    {
        if (!$value) return null;

        $decoded = Jwt::decode($value, ['algorithm' =>  new HS256Algorithm(self::getSecretKey())]);
        return $decoded['data'];
    }

    /**
     * @return array
     * @throws SettingParameterNullException
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\IO\FileNotFoundException
     * @throws \Bitrix\Main\IO\InvalidPathException
     */
    public static function getSecretKey()
    {
        $secretKey = (new Config())->getConfig('secretKey');

        if (!$secretKey)
            throw new SettingParameterNullException('secretKey');

        return $secretKey;
    }
}