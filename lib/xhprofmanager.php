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

    private function __construct()
    {
        $this->runs = new XHProfRunsDefault();
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
     * @return string
     */
    public function end(string $type, string $runId = null): string
    {
        if (!$this->isEnable() || !$this->isProfiling) {
            return '';
        }

        $xhprofData = xhprof_disable();
        $runId = $this->runs->save($xhprofData, $type, $runId);
        $this->isProfiling = false;

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

    public function deleteById(string $runId, string $type): bool
    {
        return $this->runs->delete($runId, $type);
    }

    /**
     * @throws Exception
     */
    public function deleteAll(): void
    {
        foreach ($this->runs->list() as $runData) {
            $fileName = $runData['file'] ?: '';
            if (!empty($fileName) && file_exists($fileName)) {
                unlink($fileName);
            }
        }
    }
}
