<?php

namespace Bx\XHProf;

class FunctionNode
{
    public string $name;
    /**
     * @var FunctionCall[]
     */
    private array $parentList = [];
    /**
     * @var FunctionCall[]
     */
    private array $children = [];
    private ?int $totalCallCount = null;

    private ?int $callCount = null;
    private ?int $childrenTotalCallCount = null;
    private ?int $memoryUse = null;
    private ?int $totalMemoryUse = null;
    private ?int $childrenMemoryUse = null;

    public function addParentCall(
        float $waitTime,
        float $waitTimeWithResource,
        int $memoryUse,
        int $callCount = 1,
        ?FunctionNode $parentFunction = null
    ): FunctionCall {
        $functionCall = new FunctionCall();
        $functionCall->targetFunction = $this;
        $functionCall->waitTime = $waitTime;
        $functionCall->waitTimeWithResource = $waitTimeWithResource;
        $functionCall->memoryUse = $memoryUse;
        $functionCall->callCount = $callCount;
        $functionCall->parentFunction = $parentFunction;
        $this->parentList[] = $functionCall;
        if ($parentFunction !== null) {
            $parentFunction->children = array($parentFunction->children, $functionCall);
        }

        return $functionCall;
    }
    public function addChildCall(
        float $waitTime,
        float $waitTimeWithResource,
        int $memoryUse,
        int $callCount = 1,
        ?FunctionNode $targetFunction = null
    ): FunctionCall {
        $functionCall = new FunctionCall();
        $functionCall->parentFunction = $this;
        $functionCall->waitTime = $waitTime;
        $functionCall->waitTimeWithResource = $waitTimeWithResource;
        $functionCall->memoryUse = $memoryUse;
        $functionCall->callCount = $callCount;
        $functionCall->targetFunction = $targetFunction;
        $this->children[] = $functionCall;
        if ($targetFunction !== null) {
            $targetFunction->parentList = array($targetFunction->parentList, $functionCall);
        }

        return $functionCall;
    }

    public function getWaitTimePercentFromValue(float $waitTime): float
    {
        if ($waitTime == 0) {
            return 0;
        }
        return $this->getWaitTime() / $waitTime * 100;
    }

    /**
     * Время выполнения функции без учета времени дочерних функций
     * @return float
     */
    public function getWaitTime(): float
    {
        return $this->getTotalWaiTime() - $this->getChildrenTotalWaitTime();
    }

    /**
     * Общее время выполнения
     * @return float
     */
    public function getTotalWaiTime(): float
    {
        $result = 0;
        foreach ($this->parentList as $parent) {
            $result += $parent->waitTime;
        }

        return $result;
    }

    /**
     * Время выполнения дочерних функций
     * @return float
     */
    public function getChildrenTotalWaitTime(): float
    {
        $result = 0;
        foreach ($this->children as $child) {
            $result += $child->waitTime;
        }

        return $result;
    }

    /**
     * Время выполнения функции без учета времени дочерних функций + системные ресурсы
     * @return float
     */
    public function getWaitTimeWithResource(): float
    {
        return $this->getTotalWaiTime() - $this->getChildrenTotalWaitTime();
    }

    /**
     * Общее время выполнения + системные ресурсы
     * @return float
     */
    public function getTotalWaiTimeWithResource(): float
    {
        $result = 0;
        foreach ($this->parentList as $parent) {
            $result += $parent->waitTimeWithResource;
        }

        return $result;
    }

    /**
     * Время выполнения дочерних функций + системные ресурсы
     * @return float
     */
    public function getChildrenTotalWaitTimeWithResource(): float
    {
        $result = 0;
        foreach ($this->children as $child) {
            $result += $child->waitTimeWithResource;
        }

        return $result;
    }

    public function getTotalCallCount(): int
    {
        if ($this->totalCallCount !== null) {
            return $this->totalCallCount;
        }

        $this->totalCallCount = $this->getCallCount();
        foreach ($this->children as $child) {
            if ($child->targetFunction !== null) {
                $this->totalCallCount += $child->targetFunction->getTotalCallCount();
            }
        }

        return $this->totalCallCount;
    }

    /**
     * Общее количество вызовов текущей функции
     * @return int
     */
    public function getCallCount(): int
    {
        if ($this->callCount !== null) {
            return $this->callCount;
        }

        $this->callCount = 0;
        foreach ($this->parentList as $parent) {
            $this->callCount += $parent->callCount;
        }

        return $this->callCount;
    }

    /**
     * Общее количество вызовов дочерних функций
     * @return int
     */
    public function getChildrenTotalCallCount(): int
    {
        if ($this->childrenTotalCallCount !== null) {
            return $this->childrenTotalCallCount;
        }

        $this->childrenTotalCallCount = 0;
        foreach ($this->children as $child) {
            $this->childrenTotalCallCount += $child->callCount;
        }

        return $this->childrenTotalCallCount;
    }

    /**
     * Использование памяти без учета дочерних функций
     * @return int
     */
    public function getMemoryUse(): int
    {
        if ($this->memoryUse !== null) {
            return $this->memoryUse;
        }
        return $this->memoryUse = $this->getTotalMemoryUse() - $this->getChildrenTotalMemoryUse();
    }

    /**
     * Общее использование памяти
     * @return int
     */
    public function getTotalMemoryUse(): int
    {
        if ($this->totalMemoryUse !== null) {
            return $this->totalMemoryUse;
        }

        $this->totalMemoryUse = 0;
        foreach ($this->parentList as $parent) {
            $this->totalMemoryUse += $parent->memoryUse;
        }

        return $this->totalMemoryUse;
    }

    /**
     * Использование памяти дочерними функциями
     * @return int
     */
    public function getChildrenTotalMemoryUse(): int
    {
        if ($this->childrenMemoryUse !== null) {
            return $this->childrenMemoryUse;
        }

        $this->childrenMemoryUse = 0;
        foreach ($this->children as $child) {
            $this->childrenMemoryUse += $child->memoryUse;
        }

        return $this->childrenMemoryUse;
    }
}
