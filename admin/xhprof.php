<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
global $USER;
if(!$USER->IsAdmin()) {
    return;
}

use Bitrix\Main\Application;
use Bx\XHProf\XHProfManager;
use Bitrix\Main\Loader;

Loader::includeModule('bx.xhprof');

IncludeModuleLangFile(__FILE__);

$request = Application::getInstance()->getContext()->getRequest();
$documentRoot = Application::getDocumentRoot();
$xhprofManager = XHProfManager::instance();

$runParam = $request->getQuery('run');
$sourceParam = $request->getQuery('source');
$symbolParam = $request->getQuery('symbol');

$keySort = $request->getQuery('by') ?? 'ct';
$isAscending = $request->getQuery('order') === 'asc';

if (!empty($runParam) && !empty($sourceParam)) {
    require_once './include/_run_view.php';
} else {
    require_once './include/_run_list.php';
}

require($documentRoot.'/bitrix/modules/main/include/epilog_admin.php');
