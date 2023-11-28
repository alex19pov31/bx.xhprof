<?php

namespace Bx\XHProf;

use Bx\XHProf\Interfaces\CheckerInterface;

abstract class BaseChecker implements CheckerInterface
{
    /**
     * @var CheckerInterface
     */
    private $internalChecker;

    public function __construct(CheckerInterface $checker = null)
    {
        $this->internalChecker = $checker;
    }

    /**
     * @return bool
     */
    abstract protected function check(): bool;

    /**
     * @return bool
     */
    public function isEnable(): bool
    {
        if (!$this->check()) {
            return false;
        }

        return $this->internalChecker instanceof CheckerInterface ? $this->internalChecker->isEnable() : true;
    }
}
