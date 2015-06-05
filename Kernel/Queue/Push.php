<?php namespace Kernel\Queue;

class Push {

    public function __construct($tube, $className, $data) {
        new Pheanstalk();
        $task = serialize([
            'class' => $className,
            'data' => $data
        ]);
        $thread = new Thread();
        $tube = $thread->getTubeName($tube);
        $pheanstalk = Pheanstalk::get();
        $pheanstalk
            ->useTube($tube)
            ->put($task);
    }

}