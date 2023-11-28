<?php


namespace Bx\XHProf;


use ArrayIterator;
use Bx\XHProf\Interfaces\RunInfoInterface;
use CallbackFilterIterator;
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
    private $groupData = [];
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

    public function getGroupData(): array
    {
       return $this->groupData;
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


                $item["{$k}_p"] = $allSum[$k] > 0 ? (($item[$k]/$allSum[$k]) * 100) : 0;
                $item["ext_{$k}"] = ($value - $item['child'][$k]) ?? 0;
                $item["ext_{$k}_p"] = $allSum[$k] > 0 ? (($item["ext_{$k}"]/$allSum[$k]) * 100) : 0;
            }

            if (isset($item['child'])) {
                unset($item['child']);
            }

            if (!empty($item['child_name    '])) {
                $item['child_name'] = array_keys($item['child_name']);
            }
        }
        unset($item);

        $allowAvgKeysCalc = ['wt', 'ext_wt', 'cpu', 'ext_cpu', 'mu', 'ext_mu'];
        foreach ($result as $key => &$item) {
            $countCalls = (int) ($item['ct'] ?: 0);
            foreach ($item as $k => $value) {
                if (in_array($k, $allowAvgKeysCalc)) {
                    $item["avg_$k"] = $countCalls > 0 ? $value/$countCalls : 0;
                }
            }
        }
        unset($item);

        $this->groupData = $result;
        $this->sum = $allSum;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDataByFuncName(string $funcName): array
    {
        $result = $this->groupData[$funcName] ?: [];
        if (!empty($result)) {
            $result['key'] = $funcName;
        }
        return $result;
    }

    /**
     * @param string $funcName
     * @return RunInfoInterface|null
     */
    public function filterByParentFucName(string $funcName): ?RunInfoInterface
    {
        $data = new CallbackFilterIterator(
            new ArrayIterator($this->data),
            function ($value, $key, $iterator) use ($funcName) {
                [
                    $parent,
                    $current,
                ] = explode('==>', $key);

                return trim($funcName) === trim($parent);
            });

        return new static($data, $this->description);
    }

    /**
     * @param string $funcName
     * @return RunInfoInterface|null
     */
    public function filterByChildFucName(string $funcName): ?RunInfoInterface
    {
        $data = new CallbackFilterIterator(
            new ArrayIterator($this->data),
            function ($value, $key, $iterator) use ($funcName) {
                [
                    $parent,
                    $current,
                ] = explode('==>', $key);

                return trim($funcName) === trim($current);
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

    public function getAscByKeyFromParent(string $key): SplMinHeap
    {
        return new AscendingData($key, $this->getGroupedDataFromParent());
    }

    public function getDescByKeyFromParent(string $key): SplMaxHeap
    {
        return new DescendingData($key, $this->getGroupedDataFromParent());
    }

    public function getTotalData(): array
    {
        $result = [];
        foreach ($this->data as $item) {
            foreach ($item as $k => $value) {
                if (!array_key_exists($k, $result)){
                    $result[$k] = 0;
                }

                if ($k === 'ct') {
                    $result[$k] += (int) $value;
                } else if($value > $result[$k]) {
                    $result[$k] = $value;
                }
            }
        }
        return $result;
    }

    private function getGroupedDataFromParent(): array
    {
        $result = [];
        foreach ($this->data as $key => $item) {
            [
                $parent,
                $current
            ] = explode('==>', $key);
            $item['parent'] = $parent;
            $item['key'] = $parent;
            $item['child_name'] = [$current];

            foreach ($item as $k => $value) {
                $value = (int) $value;
                $totalValue = (int) ($this->groupData[$current][$k] ?? 0);
                $item["{$k}_p"] = $totalValue > 0 ? (($value/$totalValue) * 100) : 0;
            }
            $result[$key] = $item;
        }

        return $result;
    }
}
