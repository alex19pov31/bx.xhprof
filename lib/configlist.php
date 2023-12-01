<?php

namespace Bx\XHProf;

use Bitrix\Main\ArgumentNullException;
use Bitrix\Main\ArgumentOutOfRangeException;
use Bitrix\Main\Config\Option;

class ConfigList
{
    public const MODULE_ID = 'bx.xhprof';
    public const BASE_PATH = 'BASE_PATH';
    public const ATTRIBUTES = 'ATTRIBUTES';

    /**
     * @param string $key
     * @param mixed $default
     * @return mixed|string
     * @throws ArgumentNullException
     * @throws ArgumentOutOfRangeException
     */
    public static function get(string $key, $default = null)
    {
        $value = Option::get(static::MODULE_ID, $key, $default);
        if ($key === self::ATTRIBUTES) {
            return (array) (json_decode($value, true) ?: []);
        }
        return $value;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @return void
     * @throws ArgumentOutOfRangeException
     */
    public static function set(string $key, $value)
    {
        Option::set(static::MODULE_ID, $key, $value);
    }

}
