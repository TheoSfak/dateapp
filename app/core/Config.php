<?php
namespace App\Core;

/**
 * Configuration helper – loads config/*.php files once,
 * then provides dot-notation access: Config::get('database.host')
 */
class Config
{
    private static array $cache = [];

    public static function get(string $key, mixed $default = null): mixed
    {
        $parts = explode('.', $key, 2);
        $file  = $parts[0];

        if (!isset(self::$cache[$file])) {
            $path = __DIR__ . '/../../config/' . $file . '.php';
            self::$cache[$file] = file_exists($path) ? require $path : [];
        }

        if (!isset($parts[1])) {
            return self::$cache[$file];
        }

        return self::$cache[$file][$parts[1]] ?? $default;
    }
}
