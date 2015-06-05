<?php namespace Kernel\Queue;

class Pheanstalk {
    protected static $instance = null;

    public function __construct() {
        if (null === self::$instance) {
            self::$instance = new \Pheanstalk\Pheanstalk('127.0.0.1');
        }
    }

    /**
     * @return \Pheanstalk\Pheanstalk
     */
    public static function get() {
        return self::$instance;
    }

}