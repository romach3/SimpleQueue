<?php namespace Kernel\Queue;

use Pheanstalk\Pheanstalk;

class Push {

    public function __construct($tube, $className, $data) {
        $task = serialize([
            'class' => $className,
            'data' => $data
        ]);
        $pheanstalk = new Pheanstalk('127.0.0.1');
        $pheanstalk
            ->useTube($tube)
            ->put($task);
    }

}