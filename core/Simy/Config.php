<?php
declare(strict_types=1);

namespace Simy\Core;

class Config
{
    private static array $config = [
        'app' => [
            'debug' => false,
            'env' => 'production'
        ]
    ];

    public static function loadFromArray(array $config): void
    {
        self::$config = array_replace_recursive(self::$config, $config);
    }

    public static function get(string $key, $default = null)
    {
        $keys = explode('.', $key);
        $value = self::$config;

        foreach ($keys as $k) {
            if (!isset($value[$k])) {
                return $default;
            }
            $value = $value[$k];
        }

        return $value;
    }
}