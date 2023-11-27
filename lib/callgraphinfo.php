<?php

namespace Bx\XHProf;

use Bx\XHProf\Interfaces\RunInfoInterface;

class CallGraphInfo
{
    public float $maxWidth = 5;
    public float $maxHeight = 3.5;
    public int $maxFontsize = 35;
    public int $maxSizingRatio = 20;
    public float $threshold = 0.01;
    private RunInfoInterface $runInfo;

    public function __construct(RunInfoInterface $runInfo)
    {
        $this->runInfo = $runInfo;
    }

    public function getContent(string $type): string
    {
        return $this->getCallGraphScript();
    }

    protected function getCallGraphScript(): string
    {
        $result = 'digraph call_graph {';
        $shapeNameMap = [];
        $maxWaitTime = $this->getMaxWaitTime();
        $index = 0;
        foreach ($this->runInfo->getData() as $itemKey => $itemData) {
            $shapeName = 'N' . $index++;
            [$parentFunc, $currenFunc] = explode($itemKey, '==>');
            $currenFunc = $currenFunc ?: $parentFunc;
            $shapeNameMap[$currenFunc] = $shapeName;
            $result .= $this->createShape($currenFunc, $itemData, $shapeName, $maxWaitTime);
        }

        foreach ($this->runInfo->getData() as $itemKey => $itemData) {
            [$parentFunc, $currenFunc] = explode($itemKey, '==>');
            $parentShape = $shapeNameMap[$parentFunc] ?: null;
            $childShape = $shapeNameMap[$currenFunc] ?: null;
            if (empty($parentFunc)) {
                continue;
            }

            $countCall = $itemData['ct'] ?: 0;
            $result .= "{$parentShape} -> {$childShape}[arrowsize=1, color=grey, style=\"setlinewidth(1)\", label=\"{$countCall} calls\"];\n";
        }

        $result .= '}';
        return $result;
    }

    private function createShape(string $currenFunc, array $itemData, string $shapeName, $maxWaitTime): string
    {
        if (empty($itemData)) {
            return '';
        }


        $shapeType = $this->getShapeTypeByFunc($currenFunc);

        $groupData = $this->getGroupDataByFuncName($currenFunc);
        $label = $this->getLabelByItemData($currenFunc, $itemData, $groupData);

        $sizingFactor = $this->getSizingFactorByItemData($groupData, $maxWaitTime);
        $width = $this->getWidthBySizingFactor($sizingFactor);
        $height = $this->getHeightBySizingFactor($sizingFactor);
        $fontSize = $this->getFontSizeBySizingFactor($sizingFactor);
        $fillColor = $this->getFillColorBySizingFactor($sizingFactor);

        return $shapeName . "[shape=" . $shapeType . $label . $width . $height . $fontSize . $fillColor . "];\n";
    }

    private function getDataByItemKey(string $itemKey): array
    {
        foreach ($this->runInfo->getData() as $key => $itemData) {
            if ($itemKey === $key) {
                return $itemData;
            }
        }
        return [];
    }

    private function getGroupDataByFuncName(string $funcName): array
    {
        foreach ($this->runInfo->getGroupData() as $key => $groupData) {
            if ($funcName === $key) {
                return $groupData;
            }
        }
        return [];
    }

    private function getMaxWaitTime()
    {
        $maxWt = 0;
        foreach ($this->runInfo->getGroupData() as $groupData) {
            $currentWt = $groupData['ext_wt'] ?: 0;
            if ($currentWt > $maxWt) {
                $maxWt = $currentWt;
            }
        }
        return $maxWt;
    }

    private function getLabelByItemData(string $funcName, array $itemData, array $groupData): string
    {
        $totalData = $this->runInfo->getTotalData();
        $name = $this->getNameByItemData($funcName, $itemData);

        return ", label=\"" . $name . "\\nExcl: "
            . (sprintf("%.3f",$groupData["ext_wt"] / 1000.0)) . " ms ("
            . sprintf("%.1f%%", 100 * $groupData["ext_wt"] / $totalData["wt"])
            . ")\\n" . $itemData["ct"] . " total calls\"";
    }

    /**
     * @param float|int $sizingFactor
     * @return string
     */
    private function getWidthBySizingFactor($sizingFactor): string
    {
        return ", width=" . sprintf("%.1f", $this->maxWidth / $sizingFactor);
    }

    /**
     * @param float|int $sizingFactor
     * @return string
     */
    private function getHeightBySizingFactor($sizingFactor): string
    {
        return ", height=".sprintf("%.1f", $this->maxHeight / $sizingFactor);
    }

    /**
     * @param float|int $sizingFactor
     * @return string
     */
    private function getFontSizeBySizingFactor($sizingFactor): string
    {
        return ", fontsize=" . (int) ($this->maxFontsize / (($sizingFactor - 1) / 10 + 1));
    }

    /**
     * @param float|int $sizingFactor
     * @return string
     */
    private function getFillColorBySizingFactor($sizingFactor): string
    {
        return ($sizingFactor < 1.5) ? ", style=filled, fillcolor=red" : "";
    }

    /**
     * @param array $groupData
     * @param $maxWaitTime
     * @return float|int
     */
    private function getSizingFactorByItemData(array $groupData, $maxWaitTime)
    {
        if ($groupData["ext_wt"] == 0) {
            return $this->maxSizingRatio;
        }

        $sizingFactor = $maxWaitTime / abs($groupData["ext_wt"]) ;
        return ($sizingFactor > $this->maxSizingRatio) ? $this->maxSizingRatio : $sizingFactor;
    }

    private function getNameByItemData(string $funcName, array $itemData): string
    {
        $totalData = $this->runInfo->getTotalData();
        if ($this->isMainFunction($funcName)) {
            $name = "Total: " . ($totalData["wt"] / 1000.0) . " ms\\n";
            $name .= addslashes($funcName);
            return $name;
        }

        $waitTime = $itemData["wt"];
        $name = addslashes($funcName) . "\\nInc: " . sprintf("%.3f",$itemData["wt"] / 1000);
        $name .= !$waitTime ? "ms (0)" : " ms (" . sprintf("%.1f%%", 100 * $itemData["wt"] / $itemData["wt"]).")";

        return $name;
    }

    private function getShapeTypeByFunc(string $funcName): string
    {
        return $this->isMainFunction($funcName) ? 'octagon' : 'box';
    }

    private function isMainFunction(string $funcName): bool
    {
        return $funcName === 'main()';
    }
}
