<?php

use Bx\XHProf\XHProfManager;
use Bitrix\Main\HttpRequest;
use \Bitrix\Main\Localization\Loc;

/**
 * @var HttpRequest $request
 * @var CAdminList $adminList
 * @var mixed $runParam
 * @var mixed $keySort
 * @var bool $isAscending
 * @var XHProfManager $xhprofManager
 */

$oSort = new CAdminSorting('xhprof_list', $keySort, $isAscending ? "asc" : 'desc');
$adminList = new CAdminList('xhprof_list', $oSort);

$action = $request->getQuery('action_button');
switch ($action) {
    case 'delete':
        $rawId = $request->getQuery('ID');
        if (!empty($rawId)) {
            [
                $run,
                $type
            ] = explode('.', $rawId);

            $xhprofManager->deleteById($run, $type);
        }
        break;
}

$adminList->AddHeaders([
    [
        'id'        => 'RUN_ID',
        'content'   => Loc::getMessage('profile_id'),
        'sort'      => 'run_id',
        'default'   => true,
    ],
    [
        'id'        => 'SOURCE',
        'content'   => Loc::getMessage('source'),
        'sort'      => 'source',
        'default'   => true,
    ],
    [
        'id'        => 'DATE',
        'content'   => Loc::getMessage('date_profiling'),
        'sort'      => 'date',
        'default'   => true,
    ],
]);



foreach ($xhprofManager->getRunsList() as $run) {
    /**
     * @var DateTimeImmutable $date
     */
    $date = $run['date'];
    $link = "?run={$run['run']}&source={$run['source']}&lang=".LANG;

    $arActions = [
        ["SEPARATOR" => true],
        [
        "ICON" => "delete",
        "TEXT" => Loc::getMessage('delete'),
        "ACTION" => $adminList->ActionDoGroup("{$run['run']}.{$run['source']}", "delete")
        ]
    ];

    $row = $adminList->AddRow(false, [
        'RUN_ID' => $run['run'],
        'SOURCE' => $run['source'],
        'DATE' => $date instanceof DateTimeImmutable ? $date->format('d.m.Y H:i:s') : (string)$date,
    ], $link);

    $row->AddActions($arActions);
    $row->AddViewField('RUN_ID', "<a href=\"{$link}\">{$run['run']}</a>");
}

$adminList->CheckListMode();
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
$adminList->DisplayList();
