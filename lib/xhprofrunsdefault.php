<?php

namespace Bx\XHProf;

use Bx\XHProf\Interfaces\XHProfRunsInterface;

class XHProfRunsDefault implements XHProfRunsInterface
{
    /**
     * @var string
     */
    private $dir;
    /**
     * @var string
     */
    private $suffix = 'xhprof';

    /**
     * @return string
     */
    private function genRunId(): string
    {
        return uniqid();
    }

    /**
     * @param $run_id
     * @param $type
     * @return string
     */
    private function fileName($run_id, $type): string
    {
        $type = base64_encode($type);
        $file = "$run_id.$type." . $this->suffix;

        if (!empty($this->dir)) {
            $file = $this->dir . "/" . $file;
        }
        return $file;
    }

    /**
     * XHProfRunsDefault constructor.
     * @param string|null $dir
     */
    public function __construct($dir = null)
    {

        // if user hasn't passed a directory location,
        // we use the xhprof.output_dir ini setting
        // if specified, else we default to the directory
        // in which the error_log file resides.

        if (empty($dir)) {
            $dir = ini_get("xhprof.output_dir");
            if (empty($dir)) {
                $dir = sys_get_temp_dir();
                error_log("Warning: Must specify directory location for XHProf runs. ".
                    "Trying {$dir} as default. You can either pass the " .
                    "directory location as an argument to the constructor ".
                    "for XHProfRuns_Default() or set xhprof.output_dir ".
                    "ini param.");
            }
        }
        $this->dir = $dir;
    }

    /**
     * @param $runId
     * @param $type
     * @param $runDesc
     * @return mixed|null
     */
    public function get($runId, $type, &$runDesc)
    {
        $file_name = $this->fileName($runId, $type);

        if (!file_exists($file_name)) {
            xhprof_error("Could not find file $file_name");
            $runDesc = "Invalid Run Id = $runId";
            return null;
        }

        $contents = file_get_contents($file_name);
        $runDesc = "XHProf Run (Namespace=$type)";
        return unserialize($contents);
    }

    /**
     * @param $xhprofData
     * @param $type
     * @param null $runId
     * @return string|null
     */
    public function save($xhprofData, $type, $runId = null): ?string
    {

        // Use PHP serialize function to store the XHProf's
        // raw profiler data.
        $xhprofData = serialize($xhprofData);

        if ($runId === null) {
            $runId = $this->genRunId();
        }

        $file_name = $this->fileName($runId, $type);
        $file = fopen($file_name, 'w');

        if ($file) {
            fwrite($file, $xhprofData);
            fclose($file);
        } else {
            xhprof_error("Could not open $file_name\n");
        }

        return $runId;
    }

    /**
     * @return array
     * @throws \Exception
     */
    public function list(): array
    {
        $result = [];

        if (is_dir($this->dir)) {
            $files = glob("{$this->dir}/*.{$this->suffix}");
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });

            foreach ($files as $file) {
                list($run, $source) = explode('.', basename($file));
                $result[] = [
                    'uid' => htmlentities(basename($file)),
                    'date' => (new \DateTimeImmutable)->setTimestamp((int)filemtime($file)),
                    'run' => htmlentities($run),
                    'source' => htmlentities($source),
                    'file' => $file,
                ];
            }

            return $result;
        }
    }

    /**
     * @param $runId
     * @param $type
     * @return bool
     */
    public function delete($runId, $type): bool
    {
        $filePath = $this->fileName($runId, $type);
        if (!file_exists($filePath)) {
            return false;
        }

        return unlink($filePath);
    }
}
