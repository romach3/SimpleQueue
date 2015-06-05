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

}