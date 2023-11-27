<?php

use Bx\XHProf\AscendingData;
use Bx\XHProf\DescendingData;
use Bx\XHProf\XHProfManager;
use Bitrix\Main\HttpRequest;
use \Bitrix\Main\Localization\Loc;

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
$description = $runInfo->getDescription();
$isFirstInclude = true;

if (!empty($symbolParam)) {
    $sortingTable = $request->getQuery('table_id');

    $listName = 'xhprof_current_list';
    $title = Loc::getMessage('current_function');

    $link_title = Loc::getMessage('back');
    $link_url = 'javascript:history.back()';
    if (empty($sortingTable) || $sortingTable === $listName) {
        $data = [$runInfo->getDataByFuncName($symbolParam)];
        require '_view_table.php';
    }

    /**
     * @var AscendingData|DescendingData $originalData
     */
    $originalData = $isAscending ? $runInfo->getAscByKeyFromParent($keySort) :
        $runInfo->getDescByKeyFromParent($keySort);
    $listName = 'xhprof_parent_list';
    $title = Loc::getMessage('parent_function');

    $link_title = Loc::getMessage('back');
    $link_url = 'javascript:history.back()';
    if (empty($sortingTable) || $sortingTable === $listName) {
        $data = $originalData->filterByChild($symbolParam);
        require '_view_table.php';
    }

    $runInfo = $runInfo->filterByParentFucName($symbolParam);
    /**
     * @var AscendingData|DescendingData $originalData
     */
    $originalData = $isAscending ? $runInfo->getAscByKey($keySort) : $runInfo->getDescByKey($keySort);
    $listName = 'xhprof_children_list';
    $title = Loc::getMessage('child_function');
    if (empty($sortingTable) || $sortingTable === $listName) {
        $data = $originalData->filterByParent($symbolParam);
        require '_view_table.php';
    }
} else {
    /**
     * @var AscendingData|DescendingData $originalData
     */
    $originalData = $isAscending ? $runInfo->getAscByKey($keySort) : $runInfo->getDescByKey($keySort);
    $listName = 'xhprof_all_list';
    $data = $originalData;

    $title = Loc::getMessage('all_calls');
    $link_title = Loc::getMessage('profile_list');
    $link_url = '/bitrix/admin/xhprof.php?lang='.LANGUAGE_ID;

    $totalData = $runInfo->getTotalData();
    require '_view_table.php';
}
