<?

IncludeModuleLangFile(__FILE__);
use \Bitrix\Main\ModuleManager;

class bx_xhprof extends CModule
{
    public $MODULE_ID = "bx.xhprof";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $errors;

    public function __construct()
    {
        $this->MODULE_VERSION = "0.0.1";
        $this->MODULE_VERSION_DATE = "2020-11-28 22:03:21";
        $this->MODULE_NAME = "XHProf";
        $this->MODULE_DESCRIPTION = "XHProf";
    }

    public function DoInstall()
    {
        $this->InstallDB();
        ModuleManager::RegisterModule($this->MODULE_ID);
        return true;
    }

    public function DoUninstall()
    {
        $this->UnInstallDB();
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }
}
