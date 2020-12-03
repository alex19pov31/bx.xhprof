<?php


namespace Bx\XHProf;


use Bitrix\Main\Application;

class HttpChecker extends BaseChecker
{
    protected function check(): bool
    {
        $request = Application::getInstance()->getContext()->getRequest();
        return (int)$request->getHeader('Xhprof') === 1;
    }
}
