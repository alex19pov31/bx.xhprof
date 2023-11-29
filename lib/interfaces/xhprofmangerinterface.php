<?php

namespace Bx\XHProf\Interfaces;

interface XHProfMangerInterface
{
    /**
     * @return bool
     */
    public function isProfiling(): bool;

    /**
     * @return bool
     */
    public function isEnable(): bool;

    /**
     * @return bool
     */
    public function start(): bool;

    /**
     * @param string $type
     * @param string|null $runId
     * @return string
     */
    public function end(string $type, string $runId = null): string;

    /**
     * @param CheckerInterface $checker
     * @return mixed
     */
    public function setStrategy(CheckerInterface $checker);

    /**
     * @return array
     */
    public function getRunsList(): array;

    /**
     * @param string $runId
     * @param string $type
     * @return RunInfoInterface
     */
    public function getRunById(string $runId, string $type): RunInfoInterface;

    public function getRunData(string $runId, string $type, string &$description = ''): array;

    /**
     * @param string $runId
     * @param string $type
     * @return bool
     */
    public function deleteById(string $runId, string $type): bool;
}
