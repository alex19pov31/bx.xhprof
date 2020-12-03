<?php

namespace Bx\XHProf;


class DefaultChecker extends BaseChecker
{
    protected function check(): bool
    {
        return (bool)extension_loaded('xhprof');
    }
}
