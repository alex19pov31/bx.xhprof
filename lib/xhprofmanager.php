<?php

namespace Bx\XHProf;

use Bx\XHProf\Interfaces\CheckerInterface;
use Bx\XHProf\Interfaces\RunInfoInterface;
use Bx\XHProf\Interfaces\XHProfMangerInterface;
use Exception;

use function PHPUnit\Framework\fileExists;

class XHProfManager implements XHProfMangerInterface
{
    /**
     * @var XHProfMangerInterface
     */
    private static $instance;
    /**
     * @var XHProfRunsDefault
     */
    private $runs;

    /**
     * @var bool
     */
    private $isProfiling;
    /**
     * @var DefaultChecker
     */
    private $checker;

    private function __construct(?string $basePath = null)
    {
        $basePath = $basePath ?: ConfigList::get(ConfigList::BASE_PATH, null);
        $this->runs = new XHProfRunsDefault($basePath);
        $this->checker = new DefaultChecker();
        $this->isProfiling = false;
    }

    private function __clone()
    {
    }

    public static function instance(): XHProfMangerInterface
    {
        if (static::$instance instanceof XHProfMangerInterface) {
            return static::$instance;
        }

        return static::$instance = new static();
    }

    public function isEnable(): bool
    {
        return $this->checker->isEnable();
    }

    /**
     * @return bool
     */
    public function start(): bool
    {
        if (!$this->isEnable() || $this->isProfiling) {
            return false;
        }

        xhprof_enable(XHPROF_FLAGS_NO_BUILTINS | XHPROF_FLAGS_CPU | XHPROF_FLAGS_MEMORY);
        return $this->isProfiling = true;
    }

    /**
     * @param string $type
     * @param string|null $runId
     * @param array|null $data
     * @return string
     */
    public function end(string $type, string $runId = null, ?array $data = null): string
    {
        if (!$this->isEnable() || !$this->isProfiling) {
            return '';
        }

        $xhprofData = xhprof_disable();
        $runId = $this->runs->save($xhprofData, $type, $runId);
        $this->isProfiling = false;

        $data = $data ?: [];
        $data['originalType'] = $type;
        $fileNameWithInfo = $this->getFilenameWithInfo($runId, $type);
        $this->saveJsonDataToFile($fileNameWithInfo, $data);
        return (string)$runId;
    }

    /**
     * @return bool
     */
    public function isProfiling(): bool
    {
        return (bool)$this->isProfiling;
    }

    /**
     * @param CheckerInterface $checker
     * @return void
     */
    public function setStrategy(CheckerInterface $checker)
    {
        $this->checker = $checker;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getRunsList(): array
    {
        return $this->runs->list();
    }

    /**
     * @param string $runId
     * @param string $type
     * @return RunInfoInterface
     */
    public function getRunById(string $runId, string $type): RunInfoInterface
    {
        $description = '';
        return new RunInfo($this->getRunData($runId, $type, $description), $description);
    }

    public function getRunData(string $runId, string $type, string &$description = ''): array
    {
        return (array) ($this->runs->get($runId, $type, $description) ?: []);
    }

    /**
     * @param string $runId
     * @param string $type
     * @param string $key
     * @param mixed $value
     * @return void
     */
    public function setInfoByKey(string $runId, string $type, string $key, $value): void
    {
        $fileNameWithInfo = $this->getFilenameWithInfo($runId, $type);
        $data = $this->getJsonDataFromFile($fileNameWithInfo);
        $data[$key] = $value;
        $this->saveJsonDataToFile($fileNameWithInfo, $data);
    }

    public function setAdditionalInfo(string $runId, string $type, array $data): void
    {
        $fileNameWithInfo = $this->getFilenameWithInfo($runId, $type);
        $this->saveJsonDataToFile($fileNameWithInfo, $data);
    }

    private function saveJsonDataToFile(string $fileName, array $data): void
    {
        file_put_contents($fileName, json_encode($data, JSON_UNESCAPED_UNICODE));
    }

    /**
     * @param string $runId
     * @param string $type
     * @param string $key
     * @return mixed|null
     */
    public function getInfoByKey(string $runId, string $type, string $key)
    {
        return $this->getAdditionalInfo($runId, $type)[$key] ?: null;
    }

    public function getAdditionalInfo(string $runId, string $type): array
    {
        $fileNameWithInfo = $this->getFilenameWithInfo($runId, $type);
        return $this->getJsonDataFromFile($fileNameWithInfo);
    }

    private function getJsonDataFromFile(string $fileName): array
    {
        if (!file_exists($fileName)) {
            return [];
        }
        return json_decode(file_get_contents($fileName), true) ?: [];
    }

    public function deleteById(string $runId, string $type): bool
    {
        $fileNameWithInfo = $this->getFilenameWithInfo($runId, $type);
        if (file_exists($fileNameWithInfo)) {
            unlink($fileNameWithInfo);
        }

        return $this->runs->delete($runId, $type);
    }

    private function getFilenameWithInfo(string $runId, string $type): string
    {
        $fileName = $this->runs->fileName($runId, $type);
        return str_replace('.xhprof', '.json', $fileName);
    }

    /**
     * @throws Exception
     */
    public function deleteAll(): void
    {
        foreach ($this->runs->list() as $runData) {
            $fileName = $runData['file'] ?: '';
            $fileInfoName =  str_replace('.xhprof', '.json', $fileName);
            if (!empty($fileName) && file_exists($fileName)) {
                unlink($fileName);
                unlink($fileInfoName);
            }
        }
    }

    public function getFilePath(string $runId, string $type): string
    {
        return $this->runs->fileName($runId, $type);
    }
}
