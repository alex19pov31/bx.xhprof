<?php
if (is_file($_SERVER["DOCUMENT_ROOT"] . "/local/modules/bx.xhprof/admin/xhprof.php")) {
    /** @noinspection PhpIncludeInspection */
    require($_SERVER["DOCUMENT_ROOT"] . "/local/modules/bx.xhprof/admin/xhprof.php");
} else {
    /** @noinspection PhpIncludeInspection */
    require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/bx.xhprof/admin/xhprof.php");
}
