<?php

use Bx\XHProf\AscendingData;
use Bx\XHProf\DescendingData;
use Bx\XHProf\XHProfManager;
use Bitrix\Main\HttpRequest;

/**
 * @var HttpRequest $request
 * @var XHProfManager $xhprofManager
 * @var mixed $runParam
 * @var mixed $sourceParam
 * @var mixed $symbolParam
 * @var mixed $keySort
 * @var bool $isAscending
 */

$runInfo = $xhprofManager->getRunById($runParam, $sourceParam);
//$runInfo = $symbolParam ? $runInfo->filterByFucName($symbolParam) : $runInfo;

/**
 * @var AscendingData|DescendingData $originalData
 */
$originalData = $isAscending ? $runInfo->getAscByKey($keySort) : $runInfo->getDescByKey($keySort);
$description = $runInfo->getDescription();

if (!empty($symbolParam)) {
    $sortingTable = $request->getQuery('table_id');

    $listName = 'xhprof_parent_list';
    $title = "Родительские функции:";
    if (empty($sortingTable) || $sortingTable === $listName) {
        $data = $originalData->filterByChild($symbolParam);
        require '_view_table.php';
    }

    $listName = 'xhprof_children_list';
    $title = "Дочерние функции:";
    if (empty($sortingTable) || $sortingTable === $listName) {
        $data = $originalData->filterByParent($symbolParam);
        require '_view_table.php';
    }
} else {
    $listName = 'xhprof_all_list';
    $data = $originalData;
    $title = "Все вызовы:";
    require '_view_table.php';
}
