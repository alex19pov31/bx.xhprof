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

    public function filterByFucName(string $funcName): ?RunInfoInterface;
}
