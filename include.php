<?php

use Bx\XHProf\XHProfManager;
use Bx\XHProf\HttpChecker;
use Bx\XHProf\DefaultChecker;

$chekStrategy = new HttpChecker(new DefaultChecker());
XHProfManager::instance()->setStrategy($chekStrategy);
