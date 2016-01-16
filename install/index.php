<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Entity\Base;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Application;

Loc::loadMessages(__FILE__);

/**
 * Class shantilab_yadirectapi
 */
class shantilab_yandexdirect extends CModule
{
    /**
     * shantilab_yadirectapi constructor.
     */
    public function __construct()
    {
        $arModuleVersion = [];
        include(__DIR__ . '/version.php');

        $this->MODULE_ID = 'shantilab.yandexdirect';

        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];
        $this->MODULE_NAME = Loc::getMessage('SHANTILAB_YANDEXDIRECT_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('SHANTILAB_YANDEXDIRECT_MODULE_DESCRIPTION');

        $this->PARTNER_NAME = Loc::getMessage('SHANTILAB_YANDEXDIRECT_MODULE_PARTNER_NAME');
        $this->PARTNER_URI = 'http://shantilab.ru';

        $this->MODULE_SORT = 1;
        $this->SHOW_SUPER_ADMIN_GROUP_RIGHTS = 'Y';
        $this->MODULE_GROUP_RIGHTS = 'Y';
    }

    /**
     *
     */
    public function doInstall()
    {
        global $APPLICATION;
        if ($this->isVersionD7()){
            ModuleManager::registerModule($this->MODULE_ID);
            $this->installDB();
            $this->installEvents();
            $this->installFiles();
        }else{
            $APPLICATION->ThrowException(Loc::getMessage('SHANTILAB_YANDEXDIRECT_INSTALL_ERROR_VERSION'));
        }

        $APPLICATION->IncludeAdminFile(Loc::getMessage('SHANTILAB_YANDEXDIRECT_INSTALL_TITLE'), $this->getPath() . '/install/step.php');
    }

    /**
     *
     */
    public function doUninstall()
    {
        global $APPLICATION;
        $request = Application::getInstance()->getContext()->getRequest();

        if ($request['step'] < 2){
            $APPLICATION->IncludeAdminFile(Loc::getMessage('SHANTILAB_YANDEXDIRECT_UNINSTALL_TITLE'), $this->getPath() . '/install/unstep1.php');
        }elseif($request['step'] == 2){
            $this->uninstallEvents();
            $this->uninstallFiles();
            if ($request['savedata'] != 'Y')
                $this->uninstallDB();

            ModuleManager::unregisterModule($this->MODULE_ID);

            $APPLICATION->IncludeAdminFile(Loc::getMessage('SHANTILAB_YANDEXDIRECT_UNINSTALL_TITLE'), $this->getPath() . '/install/unstep2.php');
        }
    }

    public function isVersionD7(){
        return CheckVersion(ModuleManager::getVersion('main'), '14.00.00');
    }

    public function getPath($notDocumentRoot = false){
        $drName = dirname(__DIR__);

        if ($notDocumentRoot)
            return str_replace(Application::getDocumentRoot(), '', $drName);

        return $drName;
    }

    public function installDB(){
        Loader::includeModule($this->MODULE_ID);

        if (!Application::getConnection(\Shantilab\YandexDirect\UserTable::getConnectionName())->isTableExists(
            Base::getInstance('\Shantilab\YandexDirect\UserTable')->getDBTableName()
            )
        )
            Base::getInstance('\Shantilab\YandexDirect\UserTable')->createDbTable();
    }

    public function uninstallDB()
    {
        Loader::includeModule($this->MODULE_ID);
        Application::getConnection(\Shantilab\YandexDirect\UserTable::getConnectionName())->
            queryExecute('drop table if exists ' . Base::getInstance('\Shantilab\YandexDirect\UserTable')->getDBTableName());

        Option::delete($this->MODULE_ID);
    }

    public function installEvents(){}
    public function installFiles(){}
    public function uninstallEvents(){}
    public function uninstallFiles(){}
}
