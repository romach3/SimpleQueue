<?php namespace Kernel\Queue;

use Kernel\Helpers;

class Config {
    protected static $config;

    public function __construct() {
        if (file_exists(__DIR__.'/../../App/config.php')) {
            self::reload();
        } else {
            self::$config = [];
        }
    }

    public static function all() {
        return self::$config;
    }

    public static function get($key, $default = null) {
        return Helpers::array_get(self::$config, $key, $default);
    }

    public static function reload() {
        self::$config = include(__DIR__ . '/../../App/config.php');
    }
}