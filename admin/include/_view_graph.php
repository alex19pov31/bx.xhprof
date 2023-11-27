<?php

require_once __DIR__ . '/../../lib/old/callgraph_utils.php';
require_once __DIR__ . '/../../lib/old/xhprof_runs.php';
require_once __DIR__ . '/../../lib/old/xhprof_lib.php';

/**
 * @var \Bitrix\Main\HttpRequest $request
 */
$xhprofRuns = new XHProfRuns_Default();
$run = $request->getQuery('run');
$source = $request->getQuery('source');
$type = $request->getQuery('type') ?: 'png';
$threshold = 0.01;
$func = $request->getQuery('func') ?: '';
$critical = true;

global $APPLICATION;
$APPLICATION->RestartBuffer();
xhprof_render_image($xhprofRuns, $run, $type,
    $threshold, $func, $source, $critical);
die();
