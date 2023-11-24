<?php

/**
 * @var array $totalData
 */

use Bitrix\Main\Localization\Loc;

?>

<p><?= Loc::getMessage('calls_count') ?> - <?= number_format($totalData['ct'], 0, '', ' ') ?></p>
<p><?= Loc::getMessage('all_time_exec') ?> - <?= number_format($totalData['wt'], 0, '', ' ') ?> мкс</p>
<p><?= Loc::getMessage('time_exec') ?> - <?= number_format($totalData['cpu'], 0, '', ' ') ?> мкс</p>
<p><?= Loc::getMessage('memory_usage') ?> - <?= number_format($totalData['mu'], 0, '', ' ') ?> байт</p>
