<?php

global $USER;
global $APPLICATION;

if(!$USER->IsAdmin()) {
    return;
}

use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Config\Option;
use Bx\XHProf\ConfigList;

$mid = 'bx.xhprof';

Loc::loadMessages(__FILE__);
Loader::includeModule($mid);

function loadAndGetClassListByPath(string $path): array
{
        $classList = [];
        foreach (glob("$path/*.php") as $file) {
            $class = getClassFromFile($file);
            $classParts = explode('\\', $class);
            $classList[$class] = end($classParts);
        }

        return $classList;
}

function getClassFromFile(string $filePath): string
{
    $classes = get_declared_classes();
    /**
     * @psalm-suppress UnresolvableInclude
     */
    require_once $filePath;
    $diff = array_diff(get_declared_classes(), $classes);
    return reset($diff);
}


function getNotificationTypesForDisable() : array
{
    return [
        'planVacation' => 'planVacation',
        'vacationPeriod' => 'vacationPeriod'
    ];
}

$options = [
    [
        'tab' => "Общие настройки",
        'options' => [
            ConfigList::BASE_PATH => 'Путь для сохранения файлов xhprof (относительно корня системы)',
            ConfigList::ATTRIBUTES => [
                'label' => 'Список доп. аттрибутов для вывода',
                'name' => ConfigList::ATTRIBUTES,
                'type' => 'string',
                'multiple' => true,
            ],
        ],
    ],
];

$optionsRng = [];
$optionJson = [];
$optionNames = [];
foreach ($options as $optionTab) {
    foreach ($optionTab['options'] as $name => $value) {
        if (is_string($value)) {
            $optionNames[] = $name;
        } else if (is_array($value)) {
            $name = $value['name'] ?? null;
            if ($name) {
                $optionNames[] = $name;

                $isRng = ($value['type'] ?? '') === 'rng';
                $multiple = (bool) ($value['multiple'] ?? false);
                if ($isRng) {
                    $optionsRng[] = $name;
                }
                
                if ($multiple) {
                    $optionJson[] = $name;
                }
            }
        }
    }
}

$isSave = $_POST['save'] ?? $_POST['apply'] ?? false;
if ($isSave && check_bitrix_sessid()) {
    foreach ($optionNames as $name) {
        $value = $_POST[$name] ?? null;
        if (is_array($value)) {
            $value = array_filter($value);
        }

        $isMultiple = in_array($name, $optionJson);
        if (in_array($name, $optionsRng)) {
            $newValue = [];
            if ($isMultiple) {
                $fromValues = (array)($value['from'] ?? []);
                $toValues = (array)($value['to'] ?? []);
                foreach($fromValues as $i => $fromValue) {
                    $newValue[] = [
                        'from' => $fromValue,
                        'to' => $toValues[$i] ?? '',
                    ];
                }
            } else {
                $newValue['from'] = $value['from'] ?? '';
                $newValue['to'] = $value['to'] ?? '';
            }

            $value = json_encode($newValue);
        } elseif (in_array($name, $optionJson)) {
            $value = json_encode($value);
        }

        Option::set($mid, $name, "{$value}");
    }
}


$aTabs = array_map(
    function ($item) {
        static $i = 0;
        return [
        'ICON' => '',
        'DIV' => 'tab'.($i++),
        'TAB' => $item['tab'],
        'TITLE' => $item['tab'],
        ];
    }, $options
);

$tabControl = new CAdminTabControl('tabControl', $aTabs);
$actionUrl = $APPLICATION->GetCurPage() ."?mid=".urlencode($mid)."&lang=".LANGUAGE_ID;

?>
<form method="post" action="<?php echo $actionUrl ?>">
    <?php
    echo bitrix_sessid_post();

    $tabControl->Begin();
    foreach ($options as $optionTab) {
        $tabControl->BeginNextTab();
        foreach ($optionTab['options'] as $name => $value) {
            if (is_string($value)) {
                $optionName = $name;
                $optionLabel = $value;
                $optionType = "text";
            }
            else if (is_array($value)) {
                $optionGroup = $value['group'] ?? null;
                if ($optionGroup) {
                    echo "<tr class='heading'><td colspan='2'>{$optionGroup}</td></tr>";
                    continue;
                }

                $optionType = $value['type'] ?? 'text';

                $optionName = $value['name'] ?? null;
                if (!$optionName) {
                    continue;
                }

                $optionLabel = $value['label'] ?? $optionName;
            }

            $optionValue = (string) Option::get($mid, $optionName);
            $decodedValue = json_decode($optionValue, true) ?? null;
            if (!is_null($decodedValue) && $decodedValue !== false) {
                $optionValue = $decodedValue;
            }

            ?>
            <tr>
                <td class="adm-detail-content-cell-l">
                    <?php echo $optionLabel ?>
                </td>
                <td class="adm-detail-content-cell-r">
                    <?php
                    switch ($optionType) {
                    case 'select':
                        $selectValues = $value['values'];
                        $isAssocSelectValues = !empty(
                            array_diff_assoc(
                                array_keys($selectValues),
                                range(0, count($selectValues)-1)
                            )
                        );

                        $multiple = (bool) ($value['multiple'] ?? false);
                        $size = 1;
                        if ($multiple) {
                            $size = 5;
                            $optionName .= "[]";
                        }

                        echo "<select class='typeselect' name='{$optionName}' size='{$size}' ".($multiple ? 'multiple' : '').">";
                        foreach ($selectValues as $key => $item) {
                            if ($isAssocSelectValues) {
                                $selectOptionValue = $key;
                            }
                            else {
                                $selectOptionValue = $item;
                            }

                            if ($multiple) {
                                $selected = in_array($selectOptionValue, $optionValue) ? "selected" : "";
                            }
                            else {
                                $selected = "{$selectOptionValue}" === "{$optionValue}" ? "selected" : "";
                            }

                            echo "<option value='{$selectOptionValue}' {$selected}>{$item}</option>";
                        }
                        echo "</select>";
                        break;

                    case 'checkbox':
                        $optionValue = $optionValue ?: 'N';
                        $checked = $optionValue == 'Y' ? "checked" : "";
                        echo "
                            <input class='adm-designed-checkbox' type='checkbox' id='{$optionName}' name='{$optionName}' value='Y' {$checked}>
                            <label class='adm-designed-checkbox-label' for='{$optionName}'></label>
                            ";
                        break;

                    case 'rng':
                        $multiple = (bool) ($value['multiple'] ?? false);
                        if ($multiple) {
                            //$optionName .= "[]";
                            foreach ((array)$optionValue as $item) {
                                if (empty($item)) {
                                    continue;
                                }
                                echo "<div><input name='{$optionName}[from][]' value='{$item['from']}'><input name='{$optionName}[to][]' value='{$item['to']}'></div>";
                            }
                            echo "<div>
								<input type='button' value='Добавить' onclick='addTemplateRow(this, {});'>
								<div class='jsTemplateRow' style='display:none;'>
                                    <input name='{$optionName}[from][]' value=''>
                                    <input name='{$optionName}[to][]' value=''>
								</div>
								</div>";
                        }
                        else {
                            echo "<div><input name='{$optionName}[from]' value='{$optionValue['from']}'><input name='{$optionName}[to]' value='{$optionValue['to']}'></div>";
                        }
                        break;

                    default:
                        $multiple = (bool) ($value['multiple'] ?? false);
                        if ($multiple) {
                            $optionName .= "[]";
                            foreach ((array)$optionValue as $item) {
                                if (empty($item)) {
                                    continue;
                                }
                                echo "<div><input type='{$optionType}' name='{$optionName}' value='{$item}'></div>";
                            }
                            echo "<div>
                                    <input type='button' value='Добавить' onclick='addTemplateRow(this);'>
                                    <div class='jsTemplateRow' style='display:none;'>
                                        <input type='{$optionType}' name='{$optionName}' value=''>
                                    </div>
                                    </div>";
                        }
                        else {
                            echo "<input type='{$optionType}' name='{$optionName}' value='{$optionValue}'>";
                        }
                        break;
                    }
                    ?>
                </td>
            </tr>
            <?php
        }
    }

    $tabControl->Buttons([]);
    $tabControl->End();

    ?>
</form>
<style media="screen">
    .adm-detail-content-cell-l {
        width: 50%;
    }
    .adm-detail-content-cell-r select {
        width: auto;
        max-width: 100%;
    }
    .adm-detail-content-cell-l,
    .adm-detail-content-cell-r {
        vertical-align: top;
    }
</style>
<script type="text/javascript">
    function addTemplateRow(btn) {
        var templateRow = btn.parentNode.querySelector('.jsTemplateRow')
        if (!templateRow) {
            return;
        }

        var targetElement = btn.parentNode.parentNode;
        if (!targetElement) {
            return;
        }

        var div = document.createElement('div')
        div.innerHTML = templateRow.innerHTML
        targetElement.insertBefore(
            div, targetElement.lastElementChild
        )
    }
</script>
