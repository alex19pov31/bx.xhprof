<?php

namespace Bx\XHProf;

class FunctionCall
{
    public float $waitTime = 0;
    public float $waitTimeWithResource = 0;
    public int $callCount = 0;
    public int $memoryUse = 0;
    public ?FunctionNode $parentFunction = null;
    public ?FunctionNode $targetFunction = null;

    public function getPercentWaitTime(): float
    {
        $totalTime = $this->getTotalTime();
        if ($totalTime == 0) {
            return 0;
        }

        return $this->waitTime / $totalTime * 100;
    }

    private function getTotalTime(): float
    {
        if ($this->parentFunction !== null) {
            return $this->parentFunction->getChildrenTotalWaitTime();
        }

        if ($this->targetFunction !== null) {
            return $this->targetFunction->getTotalWaiTime();
        }

        return 0;
    }

    public function getPercentWaitTimeWithResource(): float
    {
        $totalTimeWithResource = $this->getTotalTimeWithResource();
        if ($totalTimeWithResource == 0) {
            return 0;
        }

        return $this->waitTimeWithResource / $totalTimeWithResource * 100;
    }

    private function getTotalTimeWithResource(): float
    {
        if ($this->parentFunction !== null) {
            return $this->parentFunction->getChildrenTotalWaitTimeWithResource();
        }

        if ($this->targetFunction !== null) {
            return $this->targetFunction->getTotalWaiTimeWithResource();
        }

        return 0;
    }

    public function getPercentCallCount(): float
    {
        if ($this->targetFunction === null) {
            return 0;
        }

        $totalCallCount = $this->targetFunction->getCallCount();
        if ($totalCallCount == 0) {
            return 0;
        }

        return $this->callCount / $totalCallCount * 100;
    }
}
