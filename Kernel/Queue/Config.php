<?php namespace Kernel\Queue;

class Config {
    protected static $config;

    public function __construct() {
        self::$config = include(__DIR__.'/../../App/config.php');
    }

    public static function all() {
        return self::$config;
    }

    public static function get($key, $default = null) {
        if (!isset(self::$config[$key])) {
            return $default;
        }
        return self::$config[$key];
    }
}