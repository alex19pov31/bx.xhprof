<?php

namespace Bx\XHProf;

use SplMinHeap;
use CallbackFilterIterator;

class AscendingData extends SplMinHeap
{
    /**
     * @var string
     */
    private $key;

    public function __construct(string $key, $data = null)
    {
        $this->key = $key;
        foreach ($data as $k => $value) {
            if (!array_key_exists('key', $value)) {
                $value['key'] = $k;
            }
            $this->insert($value);
        }
    }

    /**
     * @param mixed $value1
     * @param mixed $value2
     * @return float
     */
    protected function compare($value1, $value2)
    {
        $aValue = $value1[$this->key] ?? 0;
        $bValue = $value2[$this->key] ?? 0;
        if ($bValue < $aValue) {
            return -1;
        }

        if ($bValue > $aValue) {
            return 1;
        }

        return 0;
    }


    /**
     * @return CallbackFilterIterator
     */
    public function getRootList(): CallbackFilterIterator
    {
        return new CallbackFilterIterator(clone $this, function ($value, $key, $iterator) {
            return empty($value['parent']);
        });
    }

    /**
     * @param string $parent
     * @return CallbackFilterIterator
     */
    public function filterByParent(string $parent): CallbackFilterIterator
    {
        return new CallbackFilterIterator(clone $this, function ($value, $key, $iterator) use ($parent) {
            return !empty($value['parent']) && trim($parent) === trim($value['parent']);
        });
    }

    /**
     * @param string $child
     * @return CallbackFilterIterator
     */
    public function filterByChild(string $child): CallbackFilterIterator
    {
        return new CallbackFilterIterator(clone $this, function ($value, $key, $iterator) use ($child) {
            return !empty($value['child_name']) && in_array(trim($child), $value['child_name']);
        });
    }
}
