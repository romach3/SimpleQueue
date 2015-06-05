<?php namespace Kernel;

class Helpers {

    public static function jobExists($name) {
        return class_exists(self::getJobClassName($name));
    }

    public static function getJobClassName($name) {
        if (substr($name, 0, 1) !== '\\') {
            $name = '\App\Jobs\\'.$name;
        }
        return $name;
    }

    public static function array_get($array, $key, $default = null) {
        if (is_null($key)) return $array;

        if (isset($array[$key])) return $array[$key];

        foreach (explode('.', $key) as $segment)
        {
            if ( ! is_array($array) || ! array_key_exists($segment, $array))
            {
                return self::value($default);
            }

            $array = $array[$segment];
        }

        return $array;
    }

    public static function array_take(&$array, $key, $default = null) {
        if (isset($array[$key])) {
            $value = $array[$key];
            unset($array[$key]);
            return $value;
        } else {
            return $default;
        }
    }

    public static function value($value)
    {
        return $value instanceof \Closure ? $value() : $value;
    }

}