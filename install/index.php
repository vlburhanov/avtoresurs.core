<?php
defined('B_PROLOG_INCLUDED') || die;

use Bitrix\Main\Application;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;

class avtoresurs_core extends CModule
{
    const MODULE_ID = 'avtoresurs.core';
    public $MODULE_ID = self::MODULE_ID;
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;

    function __construct()
    {
        $arModuleVersion = [];
        include(dirname(__FILE__) . '/version.php');
        $this->MODULE_VERSION = $arModuleVersion['VERSION'];
        $this->MODULE_VERSION_DATE = $arModuleVersion['VERSION_DATE'];

        $this->MODULE_NAME = Loc::getMessage('AVTORESURS_MODULE_NAME');
        $this->MODULE_DESCRIPTION = Loc::getMessage('AVTORESURS_MODULE_DESCRIPTION');

        $this->PARTNER_NAME = Loc::getMessage('AVTORESURS_PARTNER_NAME');
        $this->PARTNER_URI = Loc::getMessage('AVTORESURS_PARTNER_URI');
    }

    function DoInstall()
    {
        try {
            ModuleManager::registerModule(self::MODULE_ID);
            $this->InstallEvents();
        } catch (\Throwable $e) {
            ShowError($e->getMessage());
            return;
        }
    }

    function InstallEvents()
    {
        RegisterModuleDependences('crm', 'OnAfterCrmLeadAdd', $this->MODULE_ID, '\\Avtoresurs\\Core\\Handlers\\Crm\\LeadHandler', 'assignLeadToMailQueue');
    }

    function DoUninstall()
    {
        $this->UnInstallEvents();
    }


    function UnInstallEvents()
    {
        UnRegisterModuleDependences('crm', 'OnAfterCrmLeadAdd', $this->MODULE_ID, '\\Avtoresurs\\Core\\Handlers\\Crm\\LeadHandler', 'assignLeadToMailQueue');
    }
}
