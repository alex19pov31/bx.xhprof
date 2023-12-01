<?php

use Bx\XHProf\Interfaces\XHProfMangerInterface;

/**
 * @var string $runParam
 * @var string $sourceParam
 * @var XHProfMangerInterface $xhprofManager
 */

$filePath = $xhprofManager->getFilePath($runParam, $sourceParam);
$fileName = basename($filePath);

global $APPLICATION;
$APPLICATION->RestartBuffer();
header('Content-Type: application/octet-stream');
header("Content-Transfer-Encoding: Binary");
header("Content-disposition: attachment; filename=\"" . $fileName . "\"");
readfile($filePath);
die();