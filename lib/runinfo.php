<?php


namespace Bx\XHProf;


use Bx\XHProf\Interfaces\RunInfoInterface;
use SplHeap;
use SplMaxHeap;
use SplMinHeap;

class RunInfo implements RunInfoInterface
{
    private $data;
    /**
     * @var string
     */
    private $description;
    /**
     * @var array
     */
    private $groupData;
    /**
     * @var array
     */
    private $sum;

    public function __construct($data, string $description)
    {
        $this->data = $data;
        $this->description = $description;
        $this->groupData();
    }

    /**
     * @return void
     */
    private function groupData()
    {
        $result = [];
        $allSum = [];

        foreach ($this->data as $key => $item) {
            [
                $parent,
                $current
            ] = explode('==>', $key);
            $newKey = trim($current ?? $parent);

            foreach ($item as $f => $value) {
                $value = abs($value);
                $newValue = (int)$result[$newKey][$f] + (int)$value;
                $result[$newKey][$f] = $newValue;
                if ($newKey !== $parent) {
                    $newChildValue = (int)$result[$parent]['child'][$f] + (int)$value;
                    $result[$newKey]['parent'] = $parent;
                    $result[$parent]['child'][$f] = $newChildValue;
                    $result[$parent]['child_name'][$newKey] = $newKey;
                }

                if (in_array($f, ['wt', 'cpu'])) {
                    $allSum[$f] = $newValue > (int)$allSum[$f] ? $newValue : (int)$allSum[$f];
                } else {
                    $allSum[$f] = (int)$allSum[$f] + (int)$value;
                }
            }
        }

        foreach ($result as $key => &$item) {
            foreach ($item as $k => $value) {
                if (in_array($k, ['child', 'parent', 'child_name'])) {
                    continue;
                }

                $item["{$k}_p"] = ($item[$k]/$allSum[$k]) * 100;
                $item["ext_{$k}"] = ($value - $item['child'][$k]) ?? 0;
                $item["ext_{$k}_p"] = ($item["ext_{$k}"]/$allSum[$k]) * 100;
            }

            if (isset($item['child'])) {
                unset($item['child']);
            }

            if (!empty($item['child_name'])) {
                $item['child_name'] = array_keys($item['child_name']);
            }
        }
        unset($item);

        $this->groupData = $result;
        $this->sum = $allSum;
    }

    public function getData(): ?SplHeap
    {
        return $this->data;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $funcName
     * @return RunInfoInterface|null
     */
    public function filterByFucName(string $funcName): ?RunInfoInterface
    {
        $data = new \CallbackFilterIterator(
            new \ArrayIterator($this->data),
            function ($value, $key, $iterator) use ($funcName) {
                [
                    $parent,
                    $current,
                ] = explode('==>', $key);

                $currentFunc = trim($current ?? $parent);
                return trim($funcName) === trim($parent);
            });

        return new static($data, $this->description);
    }

    public function getAscByKey(string $key): SplMinHeap
    {
        return new AscendingData($key, $this->groupData);
    }

    public function getDescByKey(string $key): SplMaxHeap
    {
        return new DescendingData($key, $this->groupData);
    }
}
