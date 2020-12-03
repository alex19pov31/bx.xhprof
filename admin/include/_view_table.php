<?php

/**
 * @var HttpRequest $request
 * @var XHProfManager $xhprofManager
 * @var mixed $runParam
 * @var mixed $sourceParam
 * @var mixed $symbolParam
 * @var mixed $keySort
 * @var bool $isAscending
 * @var Iterator $data
 * @var string $listName
 * @var string $title
 */
$oSort = new CAdminSorting($listName, $keySort, $isAscending ? "asc" : 'desc');
$adminList = new CAdminList($listName, $oSort);

$adminList->AddHeaders([
    [
        'id' => 'FUNC_NAME',
        'content' => 'Имя функции/метода',
        'sort' => 'func_name',
        'default' => true,
    ],
    [
        'id' => 'CALLS',
        'content' => 'Количество вызовов',
        'sort' => 'ct',
        'default' => true,
    ],
    [
        'id' => 'CALLS_P',
        'content' => 'Вызовы (%)',
        'sort' => 'ct_p',
        'default' => true,
    ],
    [
        'id' => 'IN_TIME',
        'content' => 'Общее время выполнения + системные ресурсы',
        'sort' => 'wt',
        'default' => true,
    ],
    [
        'id' => 'IN_TIME_P',
        'content' => 'Общее время выполнения + системные ресурсы (%)',
        'sort' => 'wt_p',
        'default' => true,
    ],
    [
        'id' => 'EX_TIME',
        'content' => 'Время выполнения без вложенных функций + системные ресурсы',
        'sort' => 'ext_wt',
        'default' => true,
    ],
    [
        'id' => 'EX_TIME_P',
        'content' => 'Время выполнения без вложенных функций + системные ресурсы (%)',
        'sort' => 'ext_wt_p',
        'default' => true,
    ],
    [
        'id' => 'IN_CPU',
        'content' => 'Общее время выполнения',
        'sort' => 'cpu',
        'default' => true,
    ],
    [
        'id' => 'IN_CPU_P',
        'content' => 'Общее время выполнения (%)',
        'sort' => 'cpu_p',
        'default' => true,
    ],
    [
        'id' => 'EX_CPU',
        'content' => 'Время выполнения без вложенных функций',
        'sort' => 'ext_cpu',
        'default' => true,
    ],
    [
        'id' => 'EX_CPU_P',
        'content' => 'Общее время выполнения без вложенных функций (%)',
        'sort' => 'ext_cpu_p',
        'default' => true,
    ],
    [
        'id' => 'IN_MEM',
        'content' => 'Использование памяти',
        'sort' => 'mu',
        'default' => true,
    ],
    [
        'id' => 'IN_MEM_P',
        'content' => 'Использование памяти (%)',
        'sort' => 'mu_p',
        'default' => true,
    ],
    [
        'id' => 'EX_MEM',
        'content' => 'Использование памяти без вложенных функций',
        'sort' => 'ext_mu',
        'default' => true,
    ],
    [
        'id' => 'EX_MEM_P',
        'content' => 'Использование памяти без вложенных функций (%)',
        'sort' => 'ext_mu_p',
        'default' => true,
    ],
    [
        'id' => 'IN_PEAK_MEM',
        'content' => 'Пиковое использование памяти',
        'sort' => 'pmu',
        'default' => true,
    ],
    [
        'id' => 'IN_PEAK_MEM_P',
        'content' => 'Пиковое использование памяти (%)',
        'sort' => 'pmu_p',
        'default' => true,
    ],
    [
        'id' => 'EX_PEAK_MEM',
        'content' => 'Пиковое использование памяти без вложенных функций',
        'sort' => 'ext_pmu',
        'default' => true,
    ],
    [
        'id' => 'EX_PEAK_MEM_P',
        'content' => 'Пиковое использование памяти без вложенных функций (%)',
        'sort' => 'ext_pmu_p',
        'default' => true,
    ],
]);

foreach ($data as $item) {
    $link = "?run={$runParam}&source={$sourceParam}&symbol={$item['key']}&lang=".LANG;


    $arActions = [];
    $arActions[] = array("SEPARATOR" => true);
    $arActions[] = array("ICON"=>"delete", "TEXT"=>"Удалить", "ACTION"=>$adminList->ActionDoGroup(1, "delete"));
    $arActions[] = array("ICON"=>"edit", "TEXT"=>"Редактировать",  "ACTION"=>$adminList->ActionRedirect("yci_resizer2_set_edit.php?id=1&action=edit&".bitrix_sessid_get()."&lang=".LANG.""));


    $row = $adminList->AddRow(false, [
        'FUNC_NAME' => $item['key'],
        'CALLS' => number_format($item['ct'], 0, '', ' '),
        'CALLS_P' => number_format($item['ct_p'], 2). " %",
        'IN_TIME' => number_format($item['wt'], 0, '', ' '),
        'IN_TIME_P' => number_format($item['wt_p'], 2)." %",
        'EX_TIME' => number_format($item['ext_wt'], 0, '', ' '),
        'EX_TIME_P' => number_format($item['ext_wt_p'], 2)." %",
        'IN_CPU' => number_format($item['cpu'], 0, '', ' '),
        'IN_CPU_P' => number_format($item['cpu_p'], 2)." %",
        'EX_CPU' => number_format($item['ext_cpu'], 0, '', ' '),
        'EX_CPU_P' => number_format($item['ext_cpu_p'], 2)." %",
        'IN_MEM' => number_format($item['mu'], 0, '', ' '),
        'IN_MEM_P' => number_format($item['mu_p'], 2)." %",
        'EX_MEM' => number_format($item['ext_mu'], 0, '', ' '),
        'EX_MEM_P' => number_format($item['ext_mu_p'], 2)." %",
        'IN_PEAK_MEM' => number_format($item['pmu'], 0, '', ' '),
        'IN_PEAK_MEM_P' => number_format($item['pmu_p'], 2)." %",
        'EX_PEAK_MEM' => number_format($item['ext_pmu'], 0, '', ' '),
        'EX_PEAK_MEM_P' => number_format($item['ext_pmu_p'], 2)." %",
    ], $link);
    //$row->AddActions($arActions);

    $row->AddViewField('FUNC_NAME', "<a href=\"{$link}\">{$item['key']}</a>");
}

$adminList->CheckListMode();
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
echo "<h3>{$title}</h3>";
$adminList->DisplayList();
