<?

IncludeModuleLangFile(__FILE__);
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Localization\Loc;

class bx_xhprof extends CModule
{
    public $MODULE_ID = "bx.xhprof";
    public $MODULE_VERSION;
    public $MODULE_VERSION_DATE;
    public $MODULE_NAME;
    public $MODULE_DESCRIPTION;
    public $PARTNER_NAME;
    public $PARTNER_URI;
    public $errors;

    public function __construct()
    {
        $this->MODULE_VERSION = "0.0.1";
        $this->MODULE_VERSION_DATE = "2020-11-28 22:03:21";
        $this->MODULE_NAME = "XHProf";
        $this->MODULE_DESCRIPTION = "XHProf";
        $this->PARTNER_NAME = Loc::getMessage('author');
    }

    public function DoInstall()
    {
        $this->InstallDB();
        ModuleManager::RegisterModule($this->MODULE_ID);
        CopyDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        return true;
    }

    public function DoUninstall()
    {
        DeleteDirFiles(__DIR__ . "/admin", $_SERVER["DOCUMENT_ROOT"] . "/bitrix/admin");
        ModuleManager::UnRegisterModule($this->MODULE_ID);
        return true;
    }
}
