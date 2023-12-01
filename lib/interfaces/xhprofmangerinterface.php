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
     * @param array|null $data
     * @return string
     */
    public function end(string $type, string $runId = null, ?array $data = null): string;

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

    public function getFilePath(string $runId, string $type): string;

    public function getRunData(string $runId, string $type, string &$description = ''): array;

    /**
     * @param string $runId
     * @param string $type
     * @return bool
     */
    public function deleteById(string $runId, string $type): bool;

    public function setInfoByKey(string $runId, string $type, string $key, $value): void;
    public function setAdditionalInfo(string $runId, string $type, array $data): void;

    /**
     * @param string $runId
     * @param string $type
     * @param string $key
     * @return mixed
     */
    public function getInfoByKey(string $runId, string $type, string $key);
    public function getAdditionalInfo(string $runId, string $type): array;
}
