<?php
defined('B_PROLOG_INCLUDED') and (B_PROLOG_INCLUDED === true) or die();
use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);
$aMenu = array(
    array(
        'parent_menu' => 'global_menu_services',
        'sort' => 400,
        'text' => "Xhprof",
        'title' => Loc::getMessage('menu_title'),
        'url' => 'xhprof.php?lang='.LANGUAGE_ID,
        'icon' => 'form_menu_icon',
    ),
);
return $aMenu;
