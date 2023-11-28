<?php

namespace Bx\XHProf\Interfaces;

use SplMinHeap;
use SplMaxHeap;

interface RunInfoInterface
{
    /**
     * @return mixed
     */
    public function getData();

    /**
     * @return string
     */
    public function getDescription(): string;

    public function getAscByKey(string $key): SplMinHeap;

    public function getDescByKey(string $key): SplMaxHeap;

    public function getAscByKeyFromParent(string $key): SplMinHeap;

    public function getDescByKeyFromParent(string $key): SplMaxHeap;

    public function filterByParentFucName(string $funcName): ?RunInfoInterface;
    public function filterByChildFucName(string $funcName): ?RunInfoInterface;

    public function getDataByFuncName(string $funcName): array;

    public function getTotalData(): array;
    public function getGroupData(): array;
}
