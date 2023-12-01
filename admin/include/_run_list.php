<?php

use Bitrix\Main\Context;
use Bx\XHProf\AscendingData;
use Bx\XHProf\DataListHelper;
use Bx\XHProf\DescendingData;
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

            $type = base64_decode($type);
            $xhprofManager->deleteById($run, $type);
        }
        break;
    case 'deleteAll':
        $xhprofManager->deleteAll();
        $url = Context::getCurrent()->getRequest()->getRequestedPage() ?: '';
        LocalRedirect("$url?lang=" . LANG);
}

$adminList->AddHeaders([
    [
        'id'        => 'RUN_ID',
        'content'   => Loc::getMessage('profile_id'),
        'sort'      => 'run',
        'default'   => true,
    ],
    [
        'id'        => 'SOURCE',
        'content'   => Loc::getMessage('source'),
        'sort'      => 'decodedSource',
        'default'   => true,
    ],
    [
        'id'        => 'TOTAL_CT',
        'content'   => Loc::getMessage('calls_count'),
        'sort'      => 'ct',
        'default'   => true,
    ],
    [
        'id'        => 'TOTAL_WT',
        'content'   => Loc::getMessage('all_time_exec'),
        'sort'      => 'wt',
        'default'   => true,
    ],
    [
        'id'        => 'TOTAL_MU',
        'content'   => Loc::getMessage('memory_usage'),
        'sort'      => 'mu',
        'default'   => true,
    ],
    [
        'id'        => 'DATE',
        'content'   => Loc::getMessage('date_profiling'),
        'sort'      => 'date',
        'default'   => true,
    ],
    [
        'id'        => 'GRAPH',
        'content'   => Loc::getMessage('graph'),
        'default'   => true,
    ],
    [
        'id'        => 'FILE',
        'content'   => Loc::getMessage('download'),
        'default'   => true,
    ],
]);


$dataList = [];
foreach ($xhprofManager->getRunsList() as $run) {
    $decodedSource = base64_decode($run['source'] ?: '');
    $data = $xhprofManager->getRunData($run['run'] ?: '', $decodedSource);
    $totalData = DataListHelper::getMaxValues($data);
    $dataList[] = [
        'run' => $run['run'] ?: '',
        'decodedSource' => $decodedSource,
        'source' => $run['source'] ?: '',
        'date' => $run['date'] ?: null,
        'ct' => $totalData['ct'] ?: 0,
        'wt' => $totalData['wt'] ?: 0,
        'mu' => $totalData['mu'] ?: 0,
    ];
}

$heapData = $isAscending ? new AscendingData($keySort ?: 'run', $dataList) :
    new DescendingData($keySort ?: 'run', $dataList);

foreach ($heapData as $itemData) {
    /**
     * @var DateTimeImmutable $date
     */
    $date = $itemData['date'];
    $link = "?run={$itemData['run']}&source={$itemData['source']}&lang=" . LANG;
    $graphLink = "?run={$itemData['run']}&source={$itemData['source']}&view=graph&lang=" . LANG;
    $downloadLink = "?run={$itemData['run']}&source={$itemData['source']}&view=download&lang=" . LANG;
    $arActions = [
        ["SEPARATOR" => true],
        [
            "ICON" => "delete",
            "TEXT" => Loc::getMessage('delete'),
            "ACTION" => $adminList->ActionDoGroup("{$itemData['run']}.{$itemData['source']}", "delete")
        ]
    ];

    $row = $adminList->AddRow(false, [
        'RUN_ID' => $itemData['run'],
        'SOURCE' => $itemData['decodedSource'],
        'DATE' => $date instanceof DateTimeImmutable ? $date->format('d.m.Y H:i:s') : (string)$date,
        'TOTAL_CT' => number_format($itemData['ct'], 0, '', ' '),
        'TOTAL_WT' => number_format($itemData['wt'], 0, '', ' '),
        'TOTAL_MU' => number_format($itemData['mu'], 0, '', ' '),
    ], $link);

    $row->AddActions($arActions);
    $row->AddViewField('RUN_ID', "<a href=\"{$link}\">{$itemData['run']}</a>");
    $row->AddViewField('GRAPH', "<a href=\"{$graphLink}\">Посмотреть</a> <a href=\"{$graphLink}\" download>Скачать</a>");
    $row->AddViewField('FILE', "<a href=\"{$downloadLink}\">Скачать файл xhprof</a>");
}

$adminList->CheckListMode();
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

echo "<p><a href='?action_button=deleteAll&lang=ru'>Очистить все файлы</a></p>";
$adminList->DisplayList();