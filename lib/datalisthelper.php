<?php

namespace Bx\XHProf;

class DataListHelper
{
    public static function getMaxValues(array $dataList): array
    {
        $result = [];
        foreach ($dataList as $item) {
            foreach ($item as $k => $value) {
                if (!array_key_exists($k, $result)) {
                    $result[$k] = 0;
                }

                if ($k === 'ct') {
                    $result[$k] += (int) $value;
                } elseif ($value > $result[$k]) {
                    $result[$k] = $value;
                }
            }
        }

        return $result;
    }
}
